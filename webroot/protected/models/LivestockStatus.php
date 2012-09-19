<?php

/**
 * This is the model class for table "trickleup.livestock_status".
 *
 * The followings are the available columns in table 'trickleup.livestock_status':
 * @property integer $id
 * @property integer $participant_livestock_report_id
 * @property integer $livestock_number
 * @property integer $age_months
 * @property integer $weight_kg
 * @property string $deworm
 * @property string $problem_conceiving
 * @property string $concentrate_during_pregnancy
 * @property string $miscarriage_date
 * @property string $miscarriage_reason
 * @property string $delivery_date
 * @property integer $num_kids_born_m
 * @property integer $num_kids_born_f
 * @property string $death_date
 * @property string $death_reason
 * @property string $sale_date
 * @property string $sale_price
 * @property string $livestock_type
 * @property string $unresolved_parse_errors_json
 *
 * The followings are the available model relations:
 * @property ParticipantLivestockReport $participantLivestockReport
 */
class LivestockStatus extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return LivestockStatus the static model class
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
		return 'trickleup.livestock_status';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('participant_livestock_report_id, livestock_number, age_months, weight_kg, num_kids_born_m, num_kids_born_f', 'numerical', 'integerOnly'=>true),
			array('problem_conceiving, concentrate_during_pregnancy', 'length', 'max'=>1),
			array('miscarriage_reason, death_reason, unresolved_parse_errors_json', 'length', 'max'=>255),
			array('sale_price', 'length', 'max'=>8),
			array('livestock_type', 'length', 'max'=>10),
			array('deworm, miscarriage_date, delivery_date, death_date, sale_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, participant_livestock_report_id, livestock_number, age_months, weight_kg, deworm, problem_conceiving, concentrate_during_pregnancy, miscarriage_date, miscarriage_reason, delivery_date, num_kids_born_m, num_kids_born_f, death_date, death_reason, sale_date, sale_price, livestock_type, unresolved_parse_errors_json', 'safe', 'on'=>'search'),
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
			'participantLivestockReport' => array(self::BELONGS_TO, 'ParticipantLivestockReport', 'participant_livestock_report_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'participant_livestock_report_id' => 'Participant Livestock Report',
			'livestock_number' => 'Livestock Number',
			'age_months' => 'Age Months',
			'weight_kg' => 'Weight Kg',
			'deworm' => 'Deworm',
			'problem_conceiving' => 'Problem Conceiving',
			'concentrate_during_pregnancy' => 'Concentrate During Pregnancy',
			'miscarriage_date' => 'Miscarriage Date',
			'miscarriage_reason' => 'Miscarriage Reason',
			'delivery_date' => 'Delivery Date',
			'num_kids_born_m' => 'Num Kids Born M',
			'num_kids_born_f' => 'Num Kids Born F',
			'death_date' => 'Death Date',
			'death_reason' => 'Death Reason',
			'sale_date' => 'Sale Date',
			'sale_price' => 'Sale Price',
			'livestock_type' => 'Livestock Type',
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
		$criteria->compare('participant_livestock_report_id',$this->participant_livestock_report_id);
		$criteria->compare('livestock_number',$this->livestock_number);
		$criteria->compare('age_months',$this->age_months);
		$criteria->compare('weight_kg',$this->weight_kg);
		$criteria->compare('deworm',$this->deworm,true);
		$criteria->compare('problem_conceiving',$this->problem_conceiving,true);
		$criteria->compare('concentrate_during_pregnancy',$this->concentrate_during_pregnancy,true);
		$criteria->compare('miscarriage_date',$this->miscarriage_date,true);
		$criteria->compare('miscarriage_reason',$this->miscarriage_reason,true);
		$criteria->compare('delivery_date',$this->delivery_date,true);
		$criteria->compare('num_kids_born_m',$this->num_kids_born_m);
		$criteria->compare('num_kids_born_f',$this->num_kids_born_f);
		$criteria->compare('death_date',$this->death_date,true);
		$criteria->compare('death_reason',$this->death_reason,true);
		$criteria->compare('sale_date',$this->sale_date,true);
		$criteria->compare('sale_price',$this->sale_price,true);
		$criteria->compare('livestock_type',$this->livestock_type,true);
		$criteria->compare('unresolved_parse_errors_json',$this->unresolved_parse_errors_json,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}