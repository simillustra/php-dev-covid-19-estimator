<?php
/**
 * @author S.A.V.I.O.U.R
 * @description COVID-19 CODE CHALLENGE JS VERSION ENTRY
 * @license
 */
// Defining covid-19 constants..........
define('NORMAL_INFECTION_GROWTH_RATE',10);
define('SEVERE_INFECTION_GROWTH_RATE', 50);
define('PERCENTAGE_POSITIVE_CASES',0.15);
define('PERCENTAGE_HOSPITAL_BED_AVAILABILITY',0.35);
define('PERCENTAGE_CASES_NEEDS_FOR_ICU_CARE', 0.05);
define('PERCENTAGE_CASES_NEEDS_FOR_VENTILATION', 0.02);

$sampleCaseData = new stdClass();
$responseJSON = new stdClass();
$responseJSON->data = new stdClass();
$responseJSON->impact = new stdClass();
$responseJSON->severeImpact = new stdClass();

/**
 * @function calculateCurrentlyInfected
 * @param sampleCaseData
 * @returns currentlyInfected
 * @description estimates and saves  the number of currently and severly infected people
 */

function calculateCurrentlyInfected() {
    global $sampleCaseData, $responseJSON;
    // update impact
    $saveCurrentlyInfected = $sampleCaseData->reportedCases * NORMAL_INFECTION_GROWTH_RATE;
    $responseJSON->impact->currentlyInfected = $saveCurrentlyInfected;
    // update severeImpact
    $saveSeverelyInfected = $sampleCaseData->reportedCases * SEVERE_INFECTION_GROWTH_RATE;
    $responseJSON->severeImpact->currentlyInfected = $saveSeverelyInfected;
}

/**
 * @function calculateInfectionRatesPerPeriod
 * @params numberOfDays, periodType
 * @returns infectionRatioPerPeriod
 * @description normalise the duration input to days, and then do your computation based on periods in days, weeks and months.
 */

function calculateInfectionRatesPerPeriod($numberOfDays, $periodType) {
    $infectionRatioPerPeriod = 0;
  switch ($periodType) {
      case 'days':
          $infectionRatioPerPeriod = pow(2, intval($numberOfDays / 3));
          break;
      case 'weeks':
          $infectionRatioPerPeriod = pow(2,(intval(($numberOfDays * 7) / 3)));
          break;

      default:
          $infectionRatioPerPeriod = pow(2,($numberOfDays * 10));
          break;
  }

  return $infectionRatioPerPeriod;
}

/**
 * @function calculateIAndReturnPeriods
 * @params numberOfDays, periodType
 * @returns infectionRatioPerPeriod
 * @description normalise the duration input based on periods in days, weeks and months.
 */

function calculateIAndReturnPeriods($numberOfDays, $periodType) {
    $infectionRatioPerPeriod = 0;
  switch ($periodType) {
      case 'days':
          $infectionRatioPerPeriod = $numberOfDays;
          break;
      case 'weeks':
          $infectionRatioPerPeriod = (intval($numberOfDays * 7));
          break;

      default:
          $infectionRatioPerPeriod = (intval($numberOfDays * 30));
          break;
  }

  return $infectionRatioPerPeriod;
}

/**
 * @function calculatePossibleInfectionGrowthRate
 * @param sampleCaseData
 * @returns infectionsByRequestedTime
 * @description To estimate the number of infected people 30 days from now,
 */

function calculatePossibleInfectionGrowthRate() {
      global $sampleCaseData, $responseJSON;
      
    $INFECTION_RATE_PER_PERIOD = calculateInfectionRatesPerPeriod($sampleCaseData->timeToElapse, $sampleCaseData->periodType);
    // update impact
    $saveNormalSpreadRate = $responseJSON->impact->currentlyInfected * $INFECTION_RATE_PER_PERIOD;
    $responseJSON->impact->infectionsByRequestedTime = $saveNormalSpreadRate;
    // update severeImpact
    $saveSevereSpreadRate = $responseJSON->severeImpact->currentlyInfected * $INFECTION_RATE_PER_PERIOD;
    $responseJSON->severeImpact->infectionsByRequestedTime = $saveSevereSpreadRate;
}

/**
 * @function calculateSevereCases
 * @param sampleCaseData
 * @returns severeCasesByRequestedTime
 * @description This is the estimated number of severe positive cases that will require hospitalization to recover.
 */

function calculateSevereCases() {
      global $sampleCaseData, $responseJSON;
    // update impact
    $estimatedNormalPositive = $responseJSON->impact->infectionsByRequestedTime * PERCENTAGE_POSITIVE_CASES;
    $responseJSON->impact->severeCasesByRequestedTime = $estimatedNormalPositive;

    // update severeImpact
    $estimatedSeverePositive = $responseJSON->severeImpact->infectionsByRequestedTime * PERCENTAGE_POSITIVE_CASES;
    $responseJSON->severeImpact->severeCasesByRequestedTime = $estimatedSeverePositive;
}

/**
 * @function caclulatHospitalBedsAvailability
 * @param sampleCaseData
 * @returns hospitalBedsByRequestedTime
 * @description This is the estimated a 35% bed availability in hospitals for severe COVID-19 positive patients.
 */

function caclulateHospitalBedsAvailability() {
      global $sampleCaseData, $responseJSON;
    // update impact
    $HOSPITAL_BEDS_AVAILABLE = $sampleCaseData->totalHospitalBeds * PERCENTAGE_HOSPITAL_BED_AVAILABILITY;
    $saveNormalHospitalBedAvailable = intval($HOSPITAL_BEDS_AVAILABLE - $responseJSON->impact->severeCasesByRequestedTime);
    $responseJSON->impact->hospitalBedsByRequestedTime = $saveNormalHospitalBedAvailable;
    // update severeImpact
    $saveSevereHospitalBedAvailable = intval($HOSPITAL_BEDS_AVAILABLE - $responseJSON->severeImpact->severeCasesByRequestedTime);
    $responseJSON->severeImpact->hospitalBedsByRequestedTime = $saveSevereHospitalBedAvailable;
}

/**
 * @function calculationICURequirement
 * @param sampleCaseData
 * @returns casesForICUByRequestedTime
 * @description This is the estimated number of severe positive cases that will require ICU care.
 */

function calculationICURequirement() {
      global $sampleCaseData, $responseJSON;
    // update impact
    $saveNormalCasesNeadingICUCare = intval($responseJSON->impact->infectionsByRequestedTime * PERCENTAGE_CASES_NEEDS_FOR_ICU_CARE);
    $responseJSON->impact->casesForICUByRequestedTime = $saveNormalCasesNeadingICUCare;
    // update severeImpact
    $saveSeverCasesNeadingICUCare = intval($responseJSON->severeImpact->infectionsByRequestedTime * PERCENTAGE_CASES_NEEDS_FOR_ICU_CARE);
    $responseJSON->severeImpact->casesForICUByRequestedTime = $saveSeverCasesNeadingICUCare;
}

/**
 * @function calculateVentilatorsRequired
 * @param sampleCaseData
 * @returns casesForVentilatorsByRequestedTime
 * @description This is the estimated number of severe positive cases that will require ventilators
 */

function calculateVentilatorsRequired() {
      global $sampleCaseData, $responseJSON;
    // update impact
    $saveNormalCasesNeedingVentilators = intval($responseJSON->impact->infectionsByRequestedTime * PERCENTAGE_CASES_NEEDS_FOR_VENTILATION);
    $responseJSON->impact->casesForVentilatorsByRequestedTime = $saveNormalCasesNeedingVentilators;
    // update severeImpact
    $saveSeverCasesNeedingVentilators = intval($responseJSON->severeImpact->infectionsByRequestedTime * PERCENTAGE_CASES_NEEDS_FOR_VENTILATION);
    $responseJSON->severeImpact->casesForVentilatorsByRequestedTime = $saveSeverCasesNeedingVentilators;
}

/**
 * @function calculateCostImapctOnEconomy
 * @param sampleCaseData
 * @returns dollarsInFlight
 * @description estimate how much money the economy is likely to lose over the said period.
 */

function calculateCostImapctOnEconomy() {
      global $sampleCaseData, $responseJSON;
    $PERIOD_IN_FOCUS = calculateIAndReturnPeriods($sampleCaseData->timeToElapse, $sampleCaseData->periodType);
    $MAJORITIY_WORKING_POPULATION = $sampleCaseData->region->avgDailyIncomePopulation;
    $DAILY_EARNINGS = $sampleCaseData->region->avgDailyIncomeInUSD;

    // update impact
    $saveNormalDollarsInFlight = intval(($responseJSON->impact->infectionsByRequestedTime * $MAJORITIY_WORKING_POPULATION * $DAILY_EARNINGS) / $PERIOD_IN_FOCUS);
    $responseJSON->impact->dollarsInFlight = $saveNormalDollarsInFlight;
    // update severeImpact
    $saveSeverDollarInFlight = intval(($responseJSON->severeImpact->infectionsByRequestedTime * $MAJORITIY_WORKING_POPULATION * $DAILY_EARNINGS) / $PERIOD_IN_FOCUS);
    $responseJSON->severeImpact->dollarsInFlight = $saveSeverDollarInFlight;
}

/**
 * @function loopArrayCreateObject
 * @param $array, &$obj
 * @returns dollarsInFlight
 * @description looops through the array to create object .
 */
function loopArrayCreateObject($array, &$obj)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $obj->$key = new stdClass();
            loopArrayCreateObject($value, $obj->$key);
        } else {
            $obj->$key = $value;
        }
    }
    return $obj;
}
/**
 * @function convertArrayToObject
 * @param arrayInput
 * @returns stdClass Object
 * @description converts Array To Object
 */
function convertArrayToObject($arrayInput)
{
    $newObject = new stdClass();
    return loopArrayCreateObject($arrayInput, $newObject);
}

/**
 * @function object_to_array
 * @param $data
 * @returns Array
 * @description converts onjects to Array.
 */
function object_to_array($data){
  if(is_array($data) || is_object($data)){
      $result = array();
      foreach( $data as $key => $value){
          $result[$key] = object_to_array($value);
      }
      return $result;
  }
  return $data;
}

function initCovidEstimator($data) {
      global $sampleCaseData, $responseJSON;
    if ($data !== null) {
        // initialize variables
       // print_r($data);
        $arrayToObjConvertion = convertArrayToObject($data);
        $sampleCaseData = $arrayToObjConvertion;
        $responseJSON->data = $arrayToObjConvertion;

    // compute code challenge -1
    calculateCurrentlyInfected();
    calculatePossibleInfectionGrowthRate();

    // compute code challenge -2
    calculateSevereCases();
    caclulateHospitalBedsAvailability();

    // compute code challenge -3
    calculationICURequirement();
    calculateVentilatorsRequired();
    calculateCostImapctOnEconomy();

    // return responses
    $newRes = object_to_array($responseJSON);
    return  $newRes;
  }
   // throw new Error('Error in data Entry');
}



function covid19ImpactEstimator($data)
{
  return initCovidEstimator($data);
}