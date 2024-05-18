<?php

namespace WSMD;

class WSMD_Dummy_Data {
    
    // Function to randomly select a number of term IDs from an array of term IDs
    public static function get_random_term_ids($term_ids, $count) {
        $copy = $term_ids;
        shuffle($copy);
        return array_splice($copy, 0, $count);
    }

    public static function get_members(){

        // Get available terms ids
        $available_terms = WSMD_Taxonomy::get_terms();
        $term_ids = array_map(function($term){
            return $term->term_id;
        }, $available_terms);

        return [
            '1' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.8138783, -71.2079809',
                'wsmd_occupation' => 'Graphic Designer',
                'wsmd_company' => 'Creative Designs',
                'wsmd_address' => '123 Art Lane',
                'wsmd_city' => 'Québec City',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G1R 4P1',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://creativedesigns.com',
                'wsmd_phone' => '418-555-0101',
                'wsmd_email' => 'info@creativedesigns.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '2' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.5016889, -73.567256',
                'wsmd_occupation' => 'Tech Entrepreneur',
                'wsmd_company' => 'Innovatech',
                'wsmd_address' => '400 Tech Avenue',
                'wsmd_city' => 'Montréal',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'H2Z 1G1',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://innovatech.com',
                'wsmd_phone' => '514-555-0192',
                'wsmd_email' => 'contact@innovatech.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 5)
            ],
            '3' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.4009928, -71.8824288',
                'wsmd_occupation' => 'Museum Curator',
                'wsmd_company' => 'History Québec',
                'wsmd_address' => '789 Cultural Rd',
                'wsmd_city' => 'Sherbrooke',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J1H 1K1',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://historyquebec.com',
                'wsmd_phone' => '819-555-0113',
                'wsmd_email' => 'curator@historyquebec.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 9)
            ],
            '4' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '48.4200335, -71.0524504',
                'wsmd_occupation' => 'Brewmaster',
                'wsmd_company' => 'La Saguenéenne',
                'wsmd_address' => '255 Brewery Blvd',
                'wsmd_city' => 'Saguenay',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G7H 7P2',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://lasaguenéenne.com',
                'wsmd_phone' => '418-555-0124',
                'wsmd_email' => 'brew@lasaguenéenne.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '5' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.378789, -72.733006',
                'wsmd_occupation' => 'Hotel Manager',
                'wsmd_company' => 'Château Étoile',
                'wsmd_address' => '25 Starlight Drive',
                'wsmd_city' => 'Magog',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J1X 3W9',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://chateauetoile.com',
                'wsmd_phone' => '819-555-0165',
                'wsmd_email' => 'manager@chateauetoile.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '6' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.3421266, -72.5477492',
                'wsmd_occupation' => 'Chef',
                'wsmd_company' => 'La Bonne Fourchette',
                'wsmd_address' => '500 Gourmet St',
                'wsmd_city' => 'Trois-Rivières',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G9A 5C9',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://labonnefourchette.com',
                'wsmd_phone' => '819-555-0176',
                'wsmd_email' => 'chef@labonnefourchette.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '7' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.8059354, -71.2347079',
                'wsmd_occupation' => 'Web Developer',
                'wsmd_company' => 'WebWorks',
                'wsmd_address' => '1500 Web Street',
                'wsmd_city' => 'Québec City',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G1R 5W9',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://webworks.com',
                'wsmd_phone' => '418-555-0187',
                'wsmd_email' => 'info@webworks.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 5)
            ],
            '8' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.610759, -73.555878',
                'wsmd_occupation' => 'Architect',
                'wsmd_company' => 'BuildArt',
                'wsmd_address' => '300 Design Blvd',
                'wsmd_city' => 'Montréal',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'H3G 2L4',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://buildart.com',
                'wsmd_phone' => '514-555-0112',
                'wsmd_email' => 'contact@buildart.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '9' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '48.423564, -71.062943',
                'wsmd_occupation' => 'Photographer',
                'wsmd_company' => 'Pixel Perfect',
                'wsmd_address' => '105 Photo Lane',
                'wsmd_city' => 'Saguenay',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G7H 8N4',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://pixelperfect.com',
                'wsmd_phone' => '418-555-0148',
                'wsmd_email' => 'info@pixelperfect.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '10' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.283345, -73.157935',
                'wsmd_occupation' => 'Marketing Specialist',
                'wsmd_company' => 'MarketMinds',
                'wsmd_address' => '50 Strategy Ave',
                'wsmd_city' => 'Granby',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J2G 8X7',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://marketminds.com',
                'wsmd_phone' => '450-555-0136',
                'wsmd_email' => 'info@marketminds.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '11' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.737454, -71.296968',
                'wsmd_occupation' => 'Interior Designer',
                'wsmd_company' => 'Decor Solutions',
                'wsmd_address' => '500 Design Street',
                'wsmd_city' => 'Québec City',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G1J 1S3',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://decorsolutions.com',
                'wsmd_phone' => '418-555-0190',
                'wsmd_email' => 'contact@decorsolutions.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '12' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.564601, -73.577247',
                'wsmd_occupation' => 'Event Planner',
                'wsmd_company' => 'Event Masters',
                'wsmd_address' => '900 Party Ave',
                'wsmd_city' => 'Montréal',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'H3B 3G1',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://eventmasters.com',
                'wsmd_phone' => '514-555-0191',
                'wsmd_email' => 'info@eventmasters.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '13' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.829853, -71.254028',
                'wsmd_occupation' => 'Financial Advisor',
                'wsmd_company' => 'FinancePro',
                'wsmd_address' => '1200 Money St',
                'wsmd_city' => 'Québec City',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G1K 4K9',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://financepro.com',
                'wsmd_phone' => '418-555-0193',
                'wsmd_email' => 'advisor@financepro.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '14' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.488777, -73.562457',
                'wsmd_occupation' => 'Real Estate Agent',
                'wsmd_company' => 'Property Quebec',
                'wsmd_address' => '1100 Estate Blvd',
                'wsmd_city' => 'Montréal',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'H4C 2L7',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://propertyquebec.com',
                'wsmd_phone' => '514-555-0194',
                'wsmd_email' => 'contact@propertyquebec.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '15' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.805717, -71.234780',
                'wsmd_occupation' => 'Lawyer',
                'wsmd_company' => 'Legal Experts',
                'wsmd_address' => '700 Justice Lane',
                'wsmd_city' => 'Québec City',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G1R 2R2',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://legalexperts.com',
                'wsmd_phone' => '418-555-0195',
                'wsmd_email' => 'legal@legalexperts.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 5)
            ],
            '16' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.503182, -73.569806',
                'wsmd_occupation' => 'Consultant',
                'wsmd_company' => 'Consulting Co.',
                'wsmd_address' => '650 Business Rd',
                'wsmd_city' => 'Montréal',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'H3C 1K5',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://consultingco.com',
                'wsmd_phone' => '514-555-0196',
                'wsmd_email' => 'info@consultingco.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '17' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '47.528912, -70.354637',
                'wsmd_occupation' => 'Marine Biologist',
                'wsmd_company' => 'Marine Research Inc.',
                'wsmd_address' => '200 Ocean Ave',
                'wsmd_city' => 'Baie-Comeau',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G4Z 2K1',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://marineresearch.com',
                'wsmd_phone' => '418-555-0197',
                'wsmd_email' => 'info@marineresearch.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '18' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.030298, -74.193582',
                'wsmd_occupation' => 'Ski Resort Manager',
                'wsmd_company' => 'Snowy Peaks',
                'wsmd_address' => '150 Mountain Rd',
                'wsmd_city' => 'Saint-Sauveur',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J0R 1R4',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://snowypeaks.com',
                'wsmd_phone' => '450-555-0198',
                'wsmd_email' => 'manager@snowypeaks.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '19' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.493076, -72.148179',
                'wsmd_occupation' => 'Winery Owner',
                'wsmd_company' => 'Vineyard Estates',
                'wsmd_address' => '75 Grapevine Blvd',
                'wsmd_city' => 'Granby',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J2G 8A9',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://vineyardestates.com',
                'wsmd_phone' => '450-555-0199',
                'wsmd_email' => 'info@vineyardestates.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 5)
            ],
            '20' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.763056, -71.288694',
                'wsmd_occupation' => 'College Professor',
                'wsmd_company' => 'Quebec University',
                'wsmd_address' => '600 Knowledge Dr',
                'wsmd_city' => 'Lévis',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G6V 8T2',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://quebecuniversity.com',
                'wsmd_phone' => '418-555-0200',
                'wsmd_email' => 'professor@quebecuniversity.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '21' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '48.606289, -71.648920',
                'wsmd_occupation' => 'Engineer',
                'wsmd_company' => 'Tech Solutions',
                'wsmd_address' => '1200 Industrial Blvd',
                'wsmd_city' => 'Alma',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G8B 5W1',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://techsolutions.com',
                'wsmd_phone' => '418-555-0201',
                'wsmd_email' => 'contact@techsolutions.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '22' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.658169, -73.539448',
                'wsmd_occupation' => 'Software Developer',
                'wsmd_company' => 'Code Masters',
                'wsmd_address' => '800 Innovation Park',
                'wsmd_city' => 'Laval',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'H7M 5R8',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://codemasters.com',
                'wsmd_phone' => '450-555-0202',
                'wsmd_email' => 'info@codemasters.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '23' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '48.319392, -79.028818',
                'wsmd_occupation' => 'Mining Engineer',
                'wsmd_company' => 'Northern Mines',
                'wsmd_address' => '300 Mining Rd',
                'wsmd_city' => 'Rouyn-Noranda',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J9X 5K5',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://northernmines.com',
                'wsmd_phone' => '819-555-0203',
                'wsmd_email' => 'engineer@northernmines.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '24' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.399296, -71.918942',
                'wsmd_occupation' => 'Dentist',
                'wsmd_company' => 'Sherbrooke Dental Care',
                'wsmd_address' => '700 Smile Blvd',
                'wsmd_city' => 'Sherbrooke',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J1H 4B6',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://sherbrookdental.com',
                'wsmd_phone' => '819-555-0204',
                'wsmd_email' => 'info@sherbrookdental.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '25' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.578820, -72.003609',
                'wsmd_occupation' => 'Veterinarian',
                'wsmd_company' => 'Eastern Townships Vet',
                'wsmd_address' => '900 Pet Care Rd',
                'wsmd_city' => 'Drummondville',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J2C 3T5',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://easterntownshipsvet.com',
                'wsmd_phone' => '819-555-0205',
                'wsmd_email' => 'info@easterntownshipsvet.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '26' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '47.379273, -72.230928',
                'wsmd_occupation' => 'Librarian',
                'wsmd_company' => 'Saguenay Public Library',
                'wsmd_address' => '1200 Knowledge St',
                'wsmd_city' => 'Saguenay',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G7J 3N2',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://saguenaylibrary.com',
                'wsmd_phone' => '418-555-0206',
                'wsmd_email' => 'info@saguenaylibrary.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '27' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '48.829644, -64.485001',
                'wsmd_occupation' => 'Tour Guide',
                'wsmd_company' => 'Gaspésie Adventures',
                'wsmd_address' => '100 Explorer Rd',
                'wsmd_city' => 'Gaspé',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G4X 3B1',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://gaspesieadventures.com',
                'wsmd_phone' => '418-555-0207',
                'wsmd_email' => 'info@gaspesieadventures.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '28' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '48.205884, -65.875380',
                'wsmd_occupation' => 'Hotel Owner',
                'wsmd_company' => 'Gaspésie Resort',
                'wsmd_address' => '500 Seaside Blvd',
                'wsmd_city' => 'Percé',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G0C 2L0',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://gaspesieresort.com',
                'wsmd_phone' => '418-555-0208',
                'wsmd_email' => 'info@gaspesieresort.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '29' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '47.668715, -79.013031',
                'wsmd_occupation' => 'Mining Supervisor',
                'wsmd_company' => 'Abitibi Gold Mines',
                'wsmd_address' => '300 Mining Dr',
                'wsmd_city' => 'Val-d\'Or',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J9P 4P3',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://abitibigold.com',
                'wsmd_phone' => '819-555-0209',
                'wsmd_email' => 'supervisor@abitibigold.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '30' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.767028, -71.104962',
                'wsmd_occupation' => 'Artisan Baker',
                'wsmd_company' => 'Boulangerie Québec',
                'wsmd_address' => '400 Bread St',
                'wsmd_city' => 'Beauport',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G1E 6S8',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://boulangeriequebec.com',
                'wsmd_phone' => '418-555-0210',
                'wsmd_email' => 'info@boulangeriequebec.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '31' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '48.529664, -68.525902',
                'wsmd_occupation' => 'Fishery Manager',
                'wsmd_company' => 'Gaspésie Fisheries',
                'wsmd_address' => '800 Oceanview Dr',
                'wsmd_city' => 'Matane',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G4W 3N5',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://gaspesiefisheries.com',
                'wsmd_phone' => '418-555-0211',
                'wsmd_email' => 'manager@gaspesiefisheries.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '32' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.577408, -71.545173',
                'wsmd_occupation' => 'Farm Owner',
                'wsmd_company' => 'Quebec Organic Farms',
                'wsmd_address' => '250 Farm Lane',
                'wsmd_city' => 'Thetford Mines',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G6G 3K7',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://quebecorganicfarms.com',
                'wsmd_phone' => '418-555-0212',
                'wsmd_email' => 'info@quebecorganicfarms.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '33' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.028957, -74.731205',
                'wsmd_occupation' => 'Logistics Coordinator',
                'wsmd_company' => 'Eastern Logistics',
                'wsmd_address' => '123 Freight Rd',
                'wsmd_city' => 'Cornwall',
                'wsmd_province_state' => 'Ontario',
                'wsmd_postal_zip_code' => 'K6J 1A1',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://easternlogistics.com',
                'wsmd_phone' => '613-555-0213',
                'wsmd_email' => 'info@easternlogistics.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '34' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.658043, -74.514786',
                'wsmd_occupation' => 'Retail Manager',
                'wsmd_company' => 'Ottawa Valley Retailers',
                'wsmd_address' => '200 Retail Park',
                'wsmd_city' => 'Hawkesbury',
                'wsmd_province_state' => 'Ontario',
                'wsmd_postal_zip_code' => 'K6A 1G3',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://ottawavalleyretailers.com',
                'wsmd_phone' => '613-555-0214',
                'wsmd_email' => 'manager@ottawavalleyretailers.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '35' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.135662, -74.549343',
                'wsmd_occupation' => 'Healthcare Administrator',
                'wsmd_company' => 'St. Lawrence Health',
                'wsmd_address' => '500 Wellness Blvd',
                'wsmd_city' => 'Morrisburg',
                'wsmd_province_state' => 'Ontario',
                'wsmd_postal_zip_code' => 'K0C 1X0',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://stlawrencehealth.com',
                'wsmd_phone' => '613-555-0215',
                'wsmd_email' => 'admin@stlawrencehealth.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '36' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.421530, -75.679159',
                'wsmd_occupation' => 'Research Scientist',
                'wsmd_company' => 'Ottawa Research Institute',
                'wsmd_address' => '100 Science Park',
                'wsmd_city' => 'Ottawa',
                'wsmd_province_state' => 'Ontario',
                'wsmd_postal_zip_code' => 'K1P 5M2',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://ottawaresearch.com',
                'wsmd_phone' => '613-555-0216',
                'wsmd_email' => 'scientist@ottawaresearch.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 4)
            ],
            '37' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.123123, -72.123123',
                'wsmd_occupation' => 'Software Engineer',
                'wsmd_company' => 'Tech Innovators',
                'wsmd_address' => '123 Innovation Lane',
                'wsmd_city' => 'Québec City',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G1A 2B3',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://techinnovators.com',
                'wsmd_phone' => '418-555-0234',
                'wsmd_email' => 'info@techinnovators.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '38' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.987654, -73.456789',
                'wsmd_occupation' => 'Data Analyst',
                'wsmd_company' => 'Data Insights',
                'wsmd_address' => '456 Analytics Ave',
                'wsmd_city' => 'Montréal',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'H3A 2K4',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://datainsights.com',
                'wsmd_phone' => '514-555-0456',
                'wsmd_email' => 'contact@datainsights.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ],
            '39' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '45.345678, -72.234567',
                'wsmd_occupation' => 'Marketing Manager',
                'wsmd_company' => 'Market Leaders',
                'wsmd_address' => '789 Strategy St',
                'wsmd_city' => 'Sherbrooke',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'J1K 3G5',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://marketleaders.com',
                'wsmd_phone' => '819-555-0678',
                'wsmd_email' => 'info@marketleaders.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 2)
            ],
            '40' => [
                'wsmd_visibility' => 'default',
                'wsmd_geocode' => '46.654321, -71.876543',
                'wsmd_occupation' => 'Product Manager',
                'wsmd_company' => 'Product Masters',
                'wsmd_address' => '321 Product Blvd',
                'wsmd_city' => 'Lévis',
                'wsmd_province_state' => 'Québec',
                'wsmd_postal_zip_code' => 'G6V 4H8',
                'wsmd_country' => 'Canada',
                'wsmd_website' => 'http://productmasters.com',
                'wsmd_phone' => '418-555-0890',
                'wsmd_email' => 'contact@productmasters.com',
                'wsmd_taxonomies' => self::get_random_term_ids($term_ids, 3)
            ]
        ];
    }
}
?>
