<?php

namespace HOA;

class Google
{
    static public function createContact($row)
    {
        $name = new \Google\Service\PeopleService\Name();
        $name->setGivenName($row['first_name']);
        $name->setFamilyName($row['last_name']);

        $email_address = new \Google\Service\PeopleService\EmailAddress();
        $email_address->setValue($row['email']);

        $address = new \Google\Service\PeopleService\Address();
        $address->setStreetAddress($row['house_number'] . ' ' . $row['street']);
        $address->setCity($row['city']);
        $address->setRegion($row['state']);
        $address->setPostalCode($row['zip']);

        $phone_numbers = [];
        $row['phones'] = json_decode($row['phones']);
        foreach ($row['phones'] as $type => $value) {
            $phone_number = new \Google\Service\PeopleService\PhoneNumber();
            $phone_number->setType($type);
            $phone_number->setValue(strval($value));
            $phone_numbers[] = $phone_number;
        }

        $person = new \Google\Service\PeopleService\Person();
        $person->setNames([$name]);
        $person->setEmailAddresses([$email_address]);
        $person->setAddresses([$address]);
        $person->setPhoneNumbers($phone_numbers);
        $contact_to_create = new \Google\Service\PeopleService\ContactToCreate();
        $contact_to_create->setContactPerson($person);
        return $contact_to_create;
    }


    static public function getClient($scope_or_scopes, $auth_config_path, $token_path)
    {
        $client = new \Google\Client();
        $client->setAuthConfig($auth_config_path);
        $client->setScopes($scope_or_scopes);
        $client->setAccessType('offline');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        if (file_exists($token_path)) {
            $access_token = json_decode(file_get_contents($token_path), true);
            $client->setAccessToken($access_token);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else if (php_sapi_name() != 'cli') {
                throw new \Exception('This application must be run on the command line.');
            } else {
                // Request authorization from the user.
                $auth_url = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $auth_url);
                print 'Enter verification code: ';
                $auth_code = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $access_token = $client->fetchAccessTokenWithAuthCode($auth_code);
                $client->setAccessToken($access_token);

                // Check to see if there was an error.
                if (array_key_exists('error', $access_token)) {
                    throw new \Exception(join(', ', $access_token));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($token_path))) {
                mkdir(dirname($token_path), 0700, true);
            }
            file_put_contents($token_path, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    static public function synchronizeContacts()
    {
        $client = static::getClient(
            \Google\Service\PeopleService::CONTACTS,
            Settings::get('google')['credentials'],
            Settings::get('google')['tokens']['contacts']
        );
        $service = new \Google\Service\PeopleService($client);

        $resource_names = [];
        while (true) {
            $result = $service->people_connections->listPeopleConnections('people/me', [
                'personFields' => 'emailAddresses',
                'pageSize' => 1000,
                'pageToken' => isset($result) ? $result->getNextPageToken() : null
            ]);
            foreach ($result->connections as $person) {
                $resource_names[] = $person->resourceName;
            }
            if (is_null($result->getNextPageToken())) {
                break;
            }
        }
        $request = new \Google\Service\PeopleService\BatchDeleteContactsRequest();
        $chunked_resource_names = array_chunk($resource_names, 500);
        foreach ($chunked_resource_names as $resource_names) {
            $request->setResourceNames($resource_names);
            $service->people->batchDeleteContacts($request);
        }

        $contacts = [];
        $stmt = Service::executeStatement('
SELECT
  `members`.`email`,
  `members`.`data`->>"$.first_name" AS `first_name`,
  `members`.`data`->>"$.last_name" AS `last_name`,
  COALESCE(`members`.`data`->>"$.phone", "{}") AS `phones`,
  `parcels`.`data`->>"$.house_number" AS `house_number`,
  `parcels`.`data`->>"$.street" AS `street`,
  `parcels`.`data`->>"$.city" AS `city`,
  `parcels`.`data`->>"$.state" AS `state`,
  `parcels`.`data`->>"$.zip" AS `zip`
FROM
  `' . Settings::get('table_prefix') . 'members` AS `members`
  JOIN `' . Settings::get('table_prefix') . 'parcels` AS `parcels` ON `members`.`parcel` = `parcels`.`id`
        ');
        while ($row = $stmt->fetch()) {
            $contacts[] = static::createContact($row);
        }
        $request = new \Google\Service\PeopleService\BatchCreateContactsRequest();
        $chunked_contacts = array_chunk($contacts, 200);
        foreach ($chunked_contacts as $contacts) {
            $request->setContacts($contacts);
            $service->people->batchCreateContacts($request);
        }
    }
}
