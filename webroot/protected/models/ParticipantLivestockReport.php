<?php

/**
 * This is the model class for table "trickleup.participant_livestock_report".
 *
 * The followings are the available columns in table 'trickleup.participant_livestock_report':
 * @property integer $id
 * @property integer $participant_id
 * @property integer $business_id
 * @property string $report_date
 * @property integer $report_year
 * @property integer $report_quarter
 * @property integer $shed_condition
 * @property string $maintenance_cleanliness
 * @property string $KMn04_application
 * @property string $separation_if_pregnant
 * @property string $import_date
 * @property integer $imported_by_user_id
 * @property string $imported_by_user_type
 * @property integer $validated
 * @property string $validated_date
 * @property integer $validated_by_user_id
 * @property string $validated_by_user_type
 * @property string $format_id
 * @property string $source_file_name
 * @property string $unresolved_parse_errors_json
 *
 * The followings are the available model relations:
 * @property LivestockStatus[] $livestockStatuses
 * @property Participant $participant
 * @property Business $business
 */
class ParticipantLivestockReport extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ParticipantLivestockReport the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'trickleup.participant_livestock_report';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('participant_id, business_id, report_date, report_year, report_quarter, import_date, format_id', 'required'),
			array('participant_id, business_id, report_year, report_quarter, shed_condition, imported_by_user_id, validated, validated_by_user_id', 'numerical', 'integerOnly'=>true),
			array('maintenance_cleanliness, KMn04_application, separation_if_pregnant', 'length', 'max'=>1),
			array('imported_by_user_type, validated_by_user_type', 'length', 'max'=>13),
			array('format_id, source_file_name, unresolved_parse_errors_json', 'length', 'max'=>255),
			array('validated_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, participant_id, business_id, report_date, report_year, report_quarter, shed_condition, maintenance_cleanliness, KMn04_application, separation_if_pregnant, import_date, imported_by_user_id, imported_by_user_type, validated, validated_date, validated_by_user_id, validated_by_user_type, format_id, source_file_name, unresolved_parse_errors_json', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'livestockStatuses' => array(self::HAS_MANY, 'LivestockStatus', 'participant_livestock_report_id'),
			'participant' => array(self::BELONGS_TO, 'Participant', 'participant_id'),
			'business' => array(self::BELONGS_TO, 'Business', 'business_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'participant_id' => 'Participant',
			'business_id' => 'Business',
			'report_date' => 'Report Date',
			'report_year' => 'Report Year',
			'report_quarter' => 'Report Quarter',
			'shed_condition' => 'Shed Condition',
			'maintenance_cleanliness' => 'Maintenance Cleanliness',
			'KMn04_application' => 'Kmn04 Application',
			'separation_if_pregnant' => 'Separation If Pregnant',
			'import_date' => 'Import Date',
			'imported_by_user_id' => 'Imported By User',
			'imported_by_user_type' => 'Imported By User Type',
			'validated' => 'Validated',
			'validated_date' => 'Validated Date',
			'validated_by_user_id' => 'Validated By User',
			'validated_by_user_type' => 'Validated By User Type',
			'format_id' => 'Format',
			'source_file_name' => 'Source File Name',
			'unresolved_parse_errors_json' => 'Unresolved Parse Errors Json',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('participant_id',$this->participant_id);
		$criteria->compare('business_id',$this->business_id);
		$criteria->compare('report_date',$this->report_date,true);
		$criteria->compare('report_year',$this->report_year);
		$criteria->compare('report_quarter',$this->report_quarter);
		$criteria->compare('shed_condition',$this->shed_condition);
		$criteria->compare('maintenance_cleanliness',$this->maintenance_cleanliness,true);
		$criteria->compare('KMn04_application',$this->KMn04_application,true);
		$criteria->compare('separation_if_pregnant',$this->separation_if_pregnant,true);
		$criteria->compare('import_date',$this->import_date,true);
		$criteria->compare('imported_by_user_id',$this->imported_by_user_id);
		$criteria->compare('imported_by_user_type',$this->imported_by_user_type,true);
		$criteria->compare('validated',$this->validated);
		$criteria->compare('validated_date',$this->validated_date,true);
		$criteria->compare('validated_by_user_id',$this->validated_by_user_id);
		$criteria->compare('validated_by_user_type',$this->validated_by_user_type,true);
		$criteria->compare('format_id',$this->format_id,true);
		$criteria->compare('source_file_name',$this->source_file_name,true);
		$criteria->compare('unresolved_parse_errors_json',$this->unresolved_parse_errors_json,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}