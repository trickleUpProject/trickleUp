<?php

/**
 * This is the model class for table "livestock".
 *
 * The followings are the available columns in table 'livestock':
 * @property integer $business_number
 * @property string $participant_name
 * @property integer $quarter
 * @property integer $month
 * @property integer $year
 * @property integer $livestock_number
 * @property string $livestock_type
 * @property string $gender
 * @property double $purchase_price
 * @property string $acquisition_date
 * @property string $age_at_purchase
 * @property string $pox_vaccine_yr1
 * @property string $pox_vaccine_yr2
 * @property string $ppr_vaccine_yr1
 * @property string $ppr_vaccine_yr2
 * @property string $other_vaccine_yr1
 * @property string $other_vaccine_yr2
 * @property string $swine_flu_vaccine_yr1
 * @property string $swine_flu_vaccine_yr2
 * @property string $castration
 */
class Livestock extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Livestock the static model class
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
		return 'livestock';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('business_number, participant_name, year, livestock_number, livestock_type, gender, acquisition_date', 'required'),
			array('business_number, quarter, month, year, livestock_number', 'numerical', 'integerOnly'=>true),
			array('purchase_price', 'numerical'),
			array('participant_name', 'length', 'max'=>100),
			array('livestock_type', 'length', 'max'=>10),
			array('gender', 'length', 'max'=>1),
			array('age_at_purchase', 'length', 'max'=>25),
			array('castration', 'length', 'max'=>3),
			array('pox_vaccine_yr1, pox_vaccine_yr2, ppr_vaccine_yr1, ppr_vaccine_yr2, other_vaccine_yr1, other_vaccine_yr2, swine_flu_vaccine_yr1, swine_flu_vaccine_yr2', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('business_number, participant_name, quarter, month, year, livestock_number, livestock_type, gender, purchase_price, acquisition_date, age_at_purchase, pox_vaccine_yr1, pox_vaccine_yr2, ppr_vaccine_yr1, ppr_vaccine_yr2, other_vaccine_yr1, other_vaccine_yr2, swine_flu_vaccine_yr1, swine_flu_vaccine_yr2, castration', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'business_number' => 'Business Number',
			'participant_name' => 'Participant Name',
			'quarter' => 'Quarter',
			'month' => 'Month',
			'year' => 'Year',
			'livestock_number' => 'Livestock Number',
			'livestock_type' => 'Livestock Type',
			'gender' => 'Gender',
			'purchase_price' => 'Purchase Price',
			'acquisition_date' => 'Acquisition Date',
			'age_at_purchase' => 'Age At Purchase',
			'pox_vaccine_yr1' => 'Pox Vaccine Yr1',
			'pox_vaccine_yr2' => 'Pox Vaccine Yr2',
			'ppr_vaccine_yr1' => 'Ppr Vaccine Yr1',
			'ppr_vaccine_yr2' => 'Ppr Vaccine Yr2',
			'other_vaccine_yr1' => 'Other Vaccine Yr1',
			'other_vaccine_yr2' => 'Other Vaccine Yr2',
			'swine_flu_vaccine_yr1' => 'Swine Flu Vaccine Yr1',
			'swine_flu_vaccine_yr2' => 'Swine Flu Vaccine Yr2',
			'castration' => 'Castration',
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

		$criteria->compare('business_number',$this->business_number);
		$criteria->compare('participant_name',$this->participant_name,true);
		$criteria->compare('quarter',$this->quarter);
		$criteria->compare('month',$this->month);
		$criteria->compare('year',$this->year);
		$criteria->compare('livestock_number',$this->livestock_number);
		$criteria->compare('livestock_type',$this->livestock_type,true);
		$criteria->compare('gender',$this->gender,true);
		$criteria->compare('purchase_price',$this->purchase_price);
		$criteria->compare('acquisition_date',$this->acquisition_date,true);
		$criteria->compare('age_at_purchase',$this->age_at_purchase,true);
		$criteria->compare('pox_vaccine_yr1',$this->pox_vaccine_yr1,true);
		$criteria->compare('pox_vaccine_yr2',$this->pox_vaccine_yr2,true);
		$criteria->compare('ppr_vaccine_yr1',$this->ppr_vaccine_yr1,true);
		$criteria->compare('ppr_vaccine_yr2',$this->ppr_vaccine_yr2,true);
		$criteria->compare('other_vaccine_yr1',$this->other_vaccine_yr1,true);
		$criteria->compare('other_vaccine_yr2',$this->other_vaccine_yr2,true);
		$criteria->compare('swine_flu_vaccine_yr1',$this->swine_flu_vaccine_yr1,true);
		$criteria->compare('swine_flu_vaccine_yr2',$this->swine_flu_vaccine_yr2,true);
		$criteria->compare('castration',$this->castration,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}