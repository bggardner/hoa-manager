<?php

namespace HOA;

class Service
{

    protected static $config;
    protected static $data_source;

    /**
     * =========================================================================
     * | PDO Wrapper Methods                                                   |
     * =========================================================================
     */
    protected static function getDataSource()
    {
        if (!is_a(static::$data_source, '\PDO')) {
            static::setDataSource(
                Settings::get('pdo_dsn'),
                Settings::get('pdo_username'),
                Settings::get('pdo_password')
            );
        }
        return static::$data_source;
    }

    protected static function setDataSource($dsn, $username, $password)
    {
            static::$data_source = new \PDO(
                $dsn,
                $username,
                $password
            );
            static::$data_source->setAttribute(\PDO::ATTR_EMULATE_PREPARES, TRUE);
            static::$data_source->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            static::$data_source->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    protected static function exec($stmt)
    {
        return static::getDataSource()->exec($stmt);
    }

    protected static function prepare($stmt)
    {
        return static::getDataSource()->prepare($stmt);
    }

    protected static function query($stmt)
    {
        return static::getDataSource()->query($stmt);
    }

    /**
     * =========================================================================
     * | PDOStatement Utility Methods                                          |
     * =========================================================================
     */
    public static function executeStatement($query, $values = [])
    {
        $stmt = static::prepare($query);
        foreach ($values as $key => $value) {
            $stmt->bindValue($key + 1, $value['value'], $value['type']);
        }
        $stmt->execute();
        return $stmt;
    }

    public static function insertStatement($query, $values = [])
    {
      static::executeStatement($query, $values);
      return static::getDataSource()->lastInsertId();
    }

    /**
     * =========================================================================
     * | Tree (Nested Set) Node Methods                                        |
     * =========================================================================
     */

    /**
     * Adds a new node as the left-most child of a parent in a tree
     *
     * @param string $table Name of the database table
     * @param string $name Name of the new node
     * @param int $parent ID of the parent
     */
    public static function addTreeNode($table, $name, $parent)
    {
        static::checkDuplicateTreeNode($table, $name, $parent);
        $table = '`' . Settings::get('table_prefix') . $table . '`';
        return static::insertStatement(
            '
  LOCK TABLES ' . $table . ' WRITE;
  SELECT @newLeft := `right` FROM ' . $table . ' WHERE `id` = ?;
  UPDATE ' . $table . ' SET `right` = `right` + 2 WHERE `right` >= @newLeft;
  UPDATE ' . $table . ' SET `left` = `left` + 2 WHERE `left` > @newLeft;
  INSERT INTO ' . $table . ' (`name`,`left`,`right`) VALUES (?, @newLeft, @newLeft + 1);
  UNLOCK TABLES;
            ',
            [
                ['value' => $parent, 'type' => \PDO::PARAM_INT],
                ['value' => $name, 'type' => \PDO::PARAM_STR]
            ]
        );
    }

    public static function checkDuplicateTreeNode($table, $name, $parent, $node = 0)
    {
        $table = '`' . Settings::get('table_prefix') . $table . '`';
        $stmt = static::executeStatement(
            '
  SELECT
    1
  FROM ' . $table . ' AS `nodes`
  CROSS JOIN ' . $table . ' AS `parents`
  WHERE
    `nodes`.`left` BETWEEN `parents`.`left` AND `parents`.`right`
    AND `parents`.`id` = ?
    AND `nodes`.`name` = ?
    AND `nodes`.`id` != ?
            ',
            [
                ['value' => $parent, 'type' => \PDO::PARAM_INT],
                ['value' => $name, 'type' => \PDO::PARAM_STR],
                ['value' => $node, 'type' => \PDO::PARAM_INT]
            ]
        )->fetchColumn();
        if ($stmt) {
            throw new \Exception('Tree cannot have a sibling with the same name');
        }
    }

    /**
     * Deletes a node from the tree
     *
     * @param string $table Name of the database table
     * @param int $id ID of the node
     */
    public static function deleteTreeNode($table, $id)
    {
        $table = '`' . Settings::get('table_prefix') . $table . '`';
        static::executeStatement(
            '
  LOCK TABLES ' . $table . ' WRITE;
  SELECT @myLeft := `left`, @myRight := `right`, @myWidth := `right` - `left` + 1 FROM ' . $table . ' WHERE `id` = ?;
  DELETE FROM ' . $table . ' WHERE `left` BETWEEN @myLeft AND @myRight;
  UPDATE ' . $table . ' SET `right` = `right` - @myWidth WHERE `right` > @myRight;
  UPDATE ' . $table . ' SET `left` = `left` - @myWidth WHERE `left` > @myRight;
  UNLOCK TABLES;
            ',
            [['value' => $id, 'type' => \PDO::PARAM_INT]]
        );
    }

    /**
     * Edits the node name and optionally moves the node (and its children) to a new parent
     *
     * @param string $table Name of the database table
     * @param int $id ID of the node
     * @param string $name New name for the node
     * @param int|null $parent ID of the parent if node is to be moved
     */
    public static function editTreeNode($table, $id, $name, $parent = null)
    {
        static::checkDuplicateTreeNode($table, $name, $parent, $id);
        $stmt = static::executeStatement(
            'UPDATE `' . Settings::get('table_prefix') . $table . '` SET `name` = ? WHERE `id` = ?',
            [
                ['value' => $name, 'type' => \PDO::PARAM_STR],
                ['value' => $id, 'type' => \PDO::PARAM_INT]
            ]
        );
        if (!is_null($parent)) {
            static::moveTreeNode($table, $id, $parent);
        }
    }

    /**
     * Moves a node and its children as the left-most child of a parent
     *
     * @param string $table Name of the database table
     * @param int $id ID of the node to be moved
     * @param int $parent ID of the parent
     */
    public static function moveTreeNode($table, $id, $parent)
    {
        $table = Settings::get('table_prefix') . $table;
        $invalid = static::executeStatement(
            '
  SELECT
    (SELECT `left` FROM `' . $table . '` WHERE `id` = ?) BETWEEN
      (SELECT `left` FROM `' . $table . '` WHERE `id` = ?)
      AND
      (SELECT `right` FROM `' . $table . '` WHERE `id` = ?)
            ',
            [
                ['value' => $parent, 'type' => \PDO::PARAM_INT],
                ['value' => $id, 'type' => \PDO::PARAM_INT],
                ['value' => $id, 'type' => \PDO::PARAM_INT]
            ]
        )->fetchColumn();
        if ($invalid) {
            throw new \Exception('Move failed: target parent must be ancestor.');
        }
        $stmt = static::executeStatement(
            '
  LOCK TABLES `' . $table . '` WRITE;
  SELECT @nodeLeft := `left`, @nodeRight := `right`, @nodeSize := `right` - `left` + 1 FROM `' . $table . '` WHERE `id` = ?;
  SELECT @maxRight := MAX(`right`) FROM `' . $table . '`;
  UPDATE `' . $table . '` SET `left` = `left` + @maxRight, `right` = `right` + @maxRight WHERE `left` BETWEEN @nodeLeft AND @nodeRight; # Shift sub-tree above @maxRight
  UPDATE `' . $table . '` SET `right` = `right` - @nodeSize  WHERE `right` BETWEEN @nodeRight AND @maxRight; # Same as deleting
  UPDATE `' . $table . '` SET `left` = `left` - @nodeSize  WHERE `left` BETWEEN @nodeRight AND @maxRight; # Same as deleting
  SELECT @parentLeft := `left`, @parentRight := `right` FROM `' . $table . '` WHERE `id` = ?;
  UPDATE `' . $table . '` SET `right` = `right` + @nodeSize WHERE `right` >= @parentLeft AND `right` <= @maxRight; # Same as adding
  UPDATE `' . $table . '` SET `left` = `left` + @nodeSize WHERE `left` > @parentLeft AND `left` <= @maxRight; # Same as adding
  UPDATE `' . $table . '` SET `left` = `left` - @maxRight - @nodeLeft + @parentLeft + 1, `right` = `right` - @maxRight - @nodeLeft + @parentLeft + 1 WHERE `left` > @maxRight;
  UNLOCK TABLES;
            ',
            [
                ['value' => $id, 'type' => \PDO::PARAM_INT],
                ['value' => $parent, 'type' => \PDO::PARAM_INT]
            ]
        );
    }

    /**
     * =========================================================================
     * | Utility Methods                                                       |
     * =========================================================================
     */
    public static function addUpload($file)
    {
        $mime_type = mime_content_type($file['tmp_name']);
        $hash = sha1_file($file['tmp_name']);
        if (!in_array($mime_type, Settings::get('mime_types'))) {
            throw new \Exception('Files of type ' . $mime_type . ' are not allowed');
        }
        $dir = Settings::get('uploads_path') . '/' . substr($hash, 0, 2);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $hash)) {
            throw new \Exception('Failed to save upload: ' . $file['name']);
        }
        static::executeStatement(
            '
  INSERT INTO `' . Settings::get('table_prefix') . 'uploads` (`hash`)
  VALUES (?)
  ON DUPLICATE KEY UPDATE `hash` = VALUES(`hash`)
           ',
           [['value' => $hash, 'type' => \PDO::PARAM_STR]]
        );
        return $hash;
    }

    protected static function deleteOrphanUploads()
    {
        $stmt = static::executeStatement(
            '
  SELECT *
  FROM `'. Settings::get('table_prefix') . 'uploads`
  WHERE
    `hash` NOT IN (
      SELECT `upload` FROM `' . Settings::get('table_prefix') . 'member_uploads`
    )
            '
        );
        while ($row = $stmt->fetch()) {
            $filename = substr($row['hash'], 0, 2) . '/' . $row['hash'];
            $filename = Settings::get('uploads_path') . '/' . $filename;
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
        static::executeStatement(
            '
  DELETE
  FROM `'. Settings::get('table_prefix') . 'uploads`
  WHERE
    `hash` NOT IN (
      SELECT `upload` AS `hash` FROM `' . Settings::get('table_prefix') . 'member_uploads`
    )
            '
        );
    }

    public static function updateUploads($table_prefix, $id, $uploads)
    {
        $table = '`' . Settings::get('table_prefix') . $table_prefix . '_uploads`';

        $values = [['value' => $id, 'type' => \PDO::PARAM_INT]];
        foreach ($uploads as $upload) {
            $values[] = ['value' => $id, 'type' => \PDO::PARAM_INT];
            $values[] = ['value' => $upload['hash'], 'type' => \PDO::PARAM_STR];
            $values[] = ['value' => $upload['name'], 'type' => \PDO::PARAM_STR];
        }

        $stmt = static::executeStatement(
            '
  /* LOCK TABLES ' . $table . ' WRITE; */
  DELETE FROM ' . $table . ' WHERE `' . $table_prefix . '` = ?;
  ' . (count($uploads) ? 'INSERT INTO ' . $table . ' (`' . $table_prefix . '`, `upload`, `name`)
  VALUES
    ' . implode(",\n", array_fill(0, count($uploads), '(?, ?, ?)')) . ';' : '') . '
  /* UNLOCK TABLES; */
            ',
            $values
        );
        $stmt->closeCursor(); // Otherwise unbuffered query error
        static::deleteOrphanUploads();
    }
}
