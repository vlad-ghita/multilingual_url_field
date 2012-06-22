<?php

	if( !defined('__IN_SYMPHONY__') ) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');



	require_once(EXTENSIONS.'/url_field/fields/field.url.php');
	require_once(EXTENSIONS.'/frontend_localisation/lib/class.FLang.php');



	class FieldMultilingual_URL extends FieldURL
	{

		/*------------------------------------------------------------------------------------------------*/
		/*  Definition  */
		/*------------------------------------------------------------------------------------------------*/

		public function __construct(){
			parent::__construct();

			$this->_name = 'Multilingual URL';
			$this->field_types = array('multilingual_entry_url');
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Publish  */
		/*------------------------------------------------------------------------------------------------*/

		public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $prefix = null, $postfix = null){
			parent::displayPublishPanel($wrapper, $data, $flagWithError, $prefix, $postfix);

			$wrapper->setAttribute('class', $wrapper->getAttribute('class').' field-url');
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Output  */
		/*------------------------------------------------------------------------------------------------*/

		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null) {
			if(!is_array($data) || empty($data) || is_null($data['value'])) return;

			$result = new XMLElement($this->get('element_name'));
			$result->setAttribute('type',$data['url_type']);

			switch( $data['url_type'] ){
				case 'external':
					$result->setValue($data['value']);
					break;

				case 'internal':
					$lc = FLang::getLangCode();
					$result->setAttribute('id',$data['value']);
					$related_value = $this->findRelatedValues(array($data['value']), $lc);
					$result->setValue($related_value[0]['value']);
					break;
			}

			$wrapper->appendChild($result);
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Utilities  */
		/*------------------------------------------------------------------------------------------------*/

		public function findRelatedValues(array $relation_id = array(), $lc = null){
			// 1. Get the field instances from the SBL's related_field_id's
			// FieldManager->fetch doesn't take an array of ID's (unlike other managers)
			// so instead we'll instead build a custom where to emulate the same result
			// We also cache the result of this where to prevent subsequent calls to this
			// field repeating the same query.
			$where = ' AND id IN ('.implode(',', $this->get('related_field_id')).') ';
			$fields = FieldManager::fetch(null, null, 'ASC', 'sortorder', null, null, $where);
			if( !is_array($fields) ){
				$fields = array($fields);
			}

			if( empty($fields) ) return array();

			// 2. Find all the provided `relation_id`'s related section
			// We also cache the result using the `relation_id` as identifier
			// to prevent unnecessary queries
			$relation_id = array_filter($relation_id);
			if( empty($relation_id) ) return array();


			$relation_ids = Symphony::Database()->fetch(sprintf("
				SELECT e.id, e.section_id, s.name, s.handle
				FROM `tbl_entries` AS `e`
				LEFT JOIN `tbl_sections` AS `s` ON (s.id = e.section_id)
				WHERE e.id IN (%s)
				ORDER BY `e`.creation_date DESC
				",
				implode(',', $relation_id)
			));

			// 3. Group the `relation_id`'s by section_id
			$section_ids = array();
			$section_info = array();
			foreach( $relation_ids as $relation_information ){
				$section_ids[$relation_information['section_id']][] = $relation_information['id'];

				if( !array_key_exists($relation_information['section_id'], $section_info) ){
					$section_info[$relation_information['section_id']] = array(
						'name' => $relation_information['name'],
						'handle' => $relation_information['handle']
					);
				}
			}

			if( is_null($lc) )
				$lc = Lang::get();

			if( !FLang::validateLangCode($lc) )
				$lc = FLang::getLangCode();

			// 4. Foreach Group, use the EntryManager to fetch the entry information
			// using the schema option to only return data for the related field
			$relation_data = array();
			foreach( $section_ids as $section_id => $entry_data ){
				$schema = array();
				// Get schema
				foreach( $fields as $field ){
					if( $field->get('parent_section') == $section_id ){
						$schema = array($field->get('element_name'));
						break;
					}
				}

				EntryManager::setFetchSorting('date', 'DESC');
				$entries = EntryManager::fetch(array_values($entry_data), $section_id, null, null, null, null, false, true, $schema);

				// 5. Loop over the Entries fetching URL data
				foreach( $entries as $entry ){
					$url_data = $entry->getData($field->get('id'));

					$relation_data[] = array(
						'id' => $entry->get('id'),
						'section_handle' => $section_info[$section_id]['handle'],
						'section_name' => $section_info[$section_id]['name'],
						'value' => $url_data["value-$lc"],
						'label' => $url_data["label-$lc"]
					);
				}
			}

			// 6. Return the resulting array containing the id, section_handle, section_name and value
			return $relation_data;
		}



		/*------------------------------------------------------------------------------------------------*/
		/*  Field schema  */
		/*------------------------------------------------------------------------------------------------*/

		public function appendFieldSchema($f){}

	}
