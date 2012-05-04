<?php
/**
 * Fill a line of data with the values a table, given its primary key.
 * Only one object is expected in return.
 *
 * @param DataObject $data the shell of the data object with the values for the primary key.
 * @return DataObject The complete data object.
 */
public function getDatum($data) {
	$db = $this->getAdapter();

	$tableFormat = $data->tableFormat;

	$this->logger->info('getDatum : '.$tableFormat->format);

	// Get the values from the data table
	$sql = "SELECT ".$this->genericService->buildSelect($data->getFields());
	$sql .= " FROM ".$tableFormat->schemaCode.".".$tableFormat->tableName." AS ".$tableFormat->format;
	$sql .= " WHERE(1 = 1) ".$this->genericService->buildWhere($data->infoFields);

	$this->logger->info('getDatum : '.$sql);

	$select = $db->prepare($sql);
	$select->execute();
	$row = $select->fetch();

	// Fill the values with data from the table
	foreach ($data->editableFields as $field) {
		$key = strtolower($field->getName());
		$field->value = $row[$key];

		// Store additional info for geometry type
		if ($field->unit == "GEOM") {
			$field->xmin = $row[strtolower($key).'_x_min'];
			$field->xmax = $row[strtolower($key).'_x_max'];
			$field->ymin = $row[strtolower($key).'_y_min'];
			$field->ymax = $row[strtolower($key).'_y_max'];
		} else if ($field->type == "ARRAY") {
			// For array field we transform the value in a array object
			$field->value = $this->genericService->stringToArray($field->value);

		}
	}

	// Fill the values with data from the table
	foreach ($data->getFields() as $field) {

		// Fill the value labels for the field
		$field = $this->genericService->fillValueLabel($field);

	}

	return $data;

}

/**
 * Get a line of data from a table, given its primary key.
 * A list of objects is expected in return.
 *
 * @param DataObject $data the shell of the data object with the values for the primary key.
 * @return Array[DataObject] The complete data objects.
 */
public function getData($data) {
}
