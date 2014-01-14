class AppModel extends Model {
	public $recursive = -1;

	var $actsAs = array('Containable');

	/**
	 * Specifies the conjunction of fields by which uniqueness is determined for this model (if any of them are different the record is different).
	 * @var unknown_type
	 */
	var $uniqueBy = array('name');

	/**
	 * validation function. returns true iff the record specifies a unique combination of fields.  Uniqueness is determined by the list of fields in $this->uniqueBy.
	 * @return boolean
	*/
	function isUniqueBy() {
		$combo = array();
		foreach($this->uniqueBy as $field) {
			$combo["{$this->alias}.$field"] = $this->data[$this->alias][$field];
		}
		return $this->isUnique($combo, false);
	}

	public function beforeSave($options=array())  {

		foreach ($this->hasAndBelongsToMany as $relModelName => $habtmConfig ) {
			if (!isset($habtmConfig['autoSync']) || $habtmConfig['autoSync']) {
					
				if (!empty($this->data[$relModelName])) {

					$this->synchronizeHABTM($relModelName);
				}
			}
		}
			
		return true;
	}

	function beforeFind($queryData) {
		$this->queryData = $queryData;
	}

	/**
	 * If a record for the uniqueBy fields already exists, returns the id of that recrod.  Otherwise returns null.
	 */
	public function findIdByUniqueBy($record) {
		if (isset($record[$this->name])) {
			$record = $record[$this->name];
		}
		$conds = array();
		foreach($this->uniqueBy as $field) {
			if(empty($record[$field])) {
				return false;
			}
			$conds[$field] = $record[$field];
		}
		return $this->field('id', $conds);
	}
	
	/**
	 * Makes data for HABTM relationships consistent id-wise between data to be saved (in, e.g., users_interests) and the related table (e.g. interests).
	 * If never-seen data for the related table is passed, a record is added to the related table (e.g. interests), if $syncDb is set; sets id fields is in bridge table records
	 * to the id values in the realated data (e.g. interest ids).
	 *
	 * @param unknown_type $relModelName model HABTM-ed to the model being saved (e.g. Interest if User is being saved)
	 * @param unknown_type $syncDb flag indicating whether new records shouuld be saved in the related model table if data passed is not present there.
	 */
	public function synchronizeHABTM($relModelName, $syncDb = true)
	{
		$hasIds = array();	//ids for related records.
		foreach($this->data[$relModelName] as $i => $record) {
				
			$uniqueBy = $this->{$relModelName}->uniqueBy;
			
			if (!Utility::hasEmptyField($record, $uniqueBy)) {
				if (!empty($record['id']))  {
					$hasIds[] = $record['id'];
				}
				else if ($id = $this->{$relModelName}->findIdByUniqueBy($record))	//find id based upon other fields.
				{
					$hasIds[] = $id;
				}
				else if ($syncDb)		//need a new record in related table
				{
					$this->{$relModelName}->create();
					$this->{$relModelName}->save($record);
					$hasIds[] = $this->{$relModelName}->id;
				}
			}
				
		}

		$this->data[$relModelName] = $hasIds;
	}
}