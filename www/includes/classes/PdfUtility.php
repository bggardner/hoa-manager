<?php

namespace HOA;

class PdfUtility
{
    public const FONT_FAMILY = 'Helvetica';
    public const FONT_STYLE = '';
    public const FONT_SIZE = 12;
    public const LINE_HEIGHT = self::FONT_SIZE / 72 * 1.2;

    public const PAGE_X_MARGIN = 0.75;
    public const PAGE_Y_MARGIN = 1;

    public const LABELS = [
        '8160' => [
            'columns' => 3,
            'rows' => 10,
            'left_margin' => .1875,
            'top_margin' => .5,
            'width' => 2.625,
            'height' => 1,
            'x_gap' => .125,
            'y_gap' => 0,
            'template' => 'includes/Avery8160EasyPeelAddressLabels.pdf',
            'title' => 'Avery 8160 Easy Peel Address Labels'
         ],
         '5267' => [
            'columns' => 4,
            'rows' => 20,
            'left_margin' => .3,
            'top_margin' => .5,
            'width' => 1.75,
            'height' => .5,
            'x_gap' => .3,
            'y_gap' => 0,
            'template' => 'includes/Avery5267EasyPeelReturnAddressLabels.pdf',
            'title' => 'Avery 5267 Easy Peel Return Address Labels'
         ]
    ];

    public static function createPdf()
    {
        $pdf = new \FPDF('P', 'in', 'Letter');
        $pdf->SetMargins(static::PAGE_X_MARGIN, static::PAGE_Y_MARGIN);
        $pdf->SetAutoPageBreak(false);
        static::resetFont($pdf);
        return $pdf;
    }

    public static function invoices($stmt)
    {
        $pdf = static::createPdf();
        while ($row = $stmt->fetch()) {
            $pdf->AddPage();
            static::resetFont($pdf);
            $pdf->SetXY(static::PAGE_X_MARGIN, static::PAGE_Y_MARGIN);
            Settings::get('invoice')['header']($pdf);
            $pdf->SetFont(static::FONT_FAMILY, 'B', 24);
            $pdf->SetTextColor(128);
            $pdf->SetXY(static::PAGE_X_MARGIN, static::PAGE_Y_MARGIN);
            $pdf->Cell($pdf->GetPageWidth() - 2 * static::PAGE_X_MARGIN, 0, 'INVOICE', 0, 0, 'R');
            $pdf->SetFont(static::FONT_FAMILY, 'B', static::FONT_SIZE);
            $pdf->SetTextColor(0);
            $pdf->SetY(static::PAGE_Y_MARGIN + 2.5 * static::LINE_HEIGHT);
            foreach (['Date', 'Parcel', 'Due Date'] as $line) {
                $pdf->SetX($pdf->GetPageWidth() / 2);
                $pdf->Cell($pdf->GetPageWidth() / 2 - static::PAGE_X_MARGIN - 1, static::LINE_HEIGHT, $line . ':', 0, 1, 'R');
            }
            $pdf->SetFont(static::FONT_FAMILY, static::FONT_STYLE, static::FONT_SIZE);
            $pdf->SetY(static::PAGE_Y_MARGIN + 2.5 * static::LINE_HEIGHT);
            foreach ([
                date('m/d/Y'),
                preg_replace('/(\d{3})(\d{2})(\d{3})/', '\1-\2-\3', $row['id']),
                \DateTime::createFromFormat('Y-m-d', $row['date'])->format('m/d/Y')
            ] as $line) {
                $pdf->SetX($pdf->GetPageWidth() - static::PAGE_X_MARGIN - 1);
                $pdf->Cell(1, static::LINE_HEIGHT, $line, 0, 1, 'C');
            }
            $pdf->SetXY(static::PAGE_X_MARGIN, $pdf->GetY() + 1.5 * static::LINE_HEIGHT);
            $pdf->SetFillColor(192);
            $pdf->Cell(($pdf->GetPageWidth() - 2 * static::PAGE_X_MARGIN) / 2, .25, 'BILL TO', 0, 1, 'C', true);
            $pdf->SetY($pdf->GetY() + 0.5 * static::LINE_HEIGHT);
            foreach ([$row['owner'], $row['house_number'] . ' ' . $row['street'], $row['city'] . ', ' . $row['state'] . '  ' . $row['zip']] as $line) {
                $pdf->SetX(static::PAGE_X_MARGIN);
                $pdf->Cell(0, static::LINE_HEIGHT, $line, 0, 1);
            }
            $pdf->SetY($pdf->GetY() + .25);
            $pdf->Cell(1, .25, 'DATE', 0, 0, 'C', true);
            $pdf->SetX(static::PAGE_X_MARGIN + 1);
            $pdf->Cell(4.5, .25, 'DESCRIPTION', 0, 0, 'C', true);
            $pdf->SetX(static::PAGE_X_MARGIN + 5.5);
            $pdf->Cell(1.5, .25, 'AMOUNT', 0, 1, 'C', true);
            $stmt2 =  Service::executeStatement('
SET @balance := 0;
SELECT *
FROM `' . Settings::get('table_prefix') . 'receivables`
WHERE
  `date` > (
    SELECT COALESCE(MAX(`date`), "0000-00-00")
    FROM (
      SELECT
        `date`,
        @balance := COALESCE(@balance, 0) + `amount` AS `balance`
      FROM `' . Settings::get('table_prefix') . 'receivables`
      WHERE `parcel` = ' . $row['id'] . '
      ORDER BY `date` ASC
    ) AS `t1`
    WHERE `balance` >= 0
  )
  AND `parcel` = ' . $row['id'] . '
ORDER BY `date` ASC
            ');
            $pdf->SetY($pdf->GetY() + .5 * static::LINE_HEIGHT);
            $fmt = \NumberFormatter::create('en_US', \NumberFormatter::PATTERN_DECIMAL, ' $###,###,###.00 ;($###,###,###.00)');
            $stmt2->nextRowSet();
            $balance = 0;
            while ($row2 = $stmt2->fetch()) {
                $balance += -$row2['amount'];
                $pdf->Cell(1, static::LINE_HEIGHT, \DateTime::createFromFormat('Y-m-d', $row2['date'])->format('m/d/Y'), 0, 0, 'C');
                $pdf->SetX(static::PAGE_X_MARGIN + 1);
                $pdf->Cell(4.5, static::LINE_HEIGHT, $row2['description']);
                $pdf->SetX(static::PAGE_X_MARGIN + 5.5);
                $pdf->Cell(1.5, static::LINE_HEIGHT, $fmt->format(-$row2['amount']), 0, 1, 'R');
            }
            $pdf->SetY($pdf->GetY() + .5 * static::LINE_HEIGHT);
            $pdf->Line(static::PAGE_X_MARGIN, $pdf->GetY(), $pdf->GetPageWidth() - static::PAGE_X_MARGIN, $pdf->GetY());
            $pdf->SetY($pdf->GetY() + .25 * static::LINE_HEIGHT);
            $pdf->SetFont(static::FONT_FAMILY, 'B', static::FONT_SIZE);
            $pdf->Cell(5.5, static::LINE_HEIGHT, 'Balance Due:', 0, 0, 'R');
            $pdf->SetX(static::PAGE_X_MARGIN + 5.5);
            $pdf->Cell(1.5, static::LINE_HEIGHT, $fmt->format($balance), 0, 1, 'R');
            $pdf->SetY($pdf->GetY() + .25 * static::LINE_HEIGHT);
            $pdf->Line(static::PAGE_X_MARGIN, $pdf->GetY(), $pdf->GetPageWidth() - static::PAGE_X_MARGIN, $pdf->GetY());
            static::resetFont($pdf);
            $pdf->SetY($pdf->GetY() + 2 * static::LINE_HEIGHT);
            Settings::get('invoice')['footer']($pdf);
        }
        $pdf->Output();
        exit;
    }

    public static function labels($label, $stmt, $callback, $offset = 0)
    {
        $label_params = static::LABELS[$label];
        $line_height = min(10 / 72 * 1.2, $label_params['top_margin'] - .125); // Maximum 10pt font with 1/16" padding
        $font_size = $line_height * 72 / 1.2;

        $pdf = new \setasign\Fpdi\Fpdi('P', 'in', 'Letter');
        $pdf->SetMargins($label_params['left_margin'], $label_params['top_margin']);
        $pdf->SetAutoPageBreak(false);

        $labels_per_page = $label_params['rows'] * $label_params['columns'];
        $i = $offset;
        foreach ($stmt as $row) {
            if (($i % $labels_per_page) == 0 || $pdf->PageNo() == 0) {
                $pdf->AddPage();
                if (isset($_GET['outlines'])) {
                    $pdf->setSourceFile($label_params['template']);
                    $tplId = $pdf->importPage(1);
                    $pdf->useTemplate($tplId);
                }
                $pdf->SetXY(0, ($label_params['top_margin'] - $line_height) / 2);
                $pdf->SetFont('Courier', '', $font_size);
                $pdf->Cell($pdf->GetPageWidth(), $line_height, $label_params['title'], 0, 0, 'C');
            }
            $x = $label_params['left_margin'] + ($i % $label_params['columns']) * ($label_params['width'] + $label_params['x_gap']);
            $y = $label_params['top_margin'] + ($label_params['height'] + $label_params['y_gap']) * floor(($i % $labels_per_page) / $label_params['columns']);
            $pdf->SetXY($x, $y);
            call_user_func($callback, $pdf, $row, static::LABELS[$label]);
            $i++;
        }
        $pdf->Output();
        exit;
    }

    public static function resetFont($pdf)
    {
        $pdf->SetFont(static::FONT_FAMILY, static::FONT_STYLE, static::FONT_SIZE);
    }
}
