<?php
// Defining covid-19 constants..........
define('NORMAL_INFECTION_GROWTH_RATE',10);
define('SEVERE_INFECTION_GROWTH_RATE', 50);
define('PERCENTAGE_POSITIVE_CASES',0.15);
define('PERCENTAGE_HOSPITAL_BED_AVAILABILITY',0.35);
define('PERCENTAGE_CASES_NEEDS_FOR_ICU_CARE', 0.05);
define('PERCENTAGE_CASES_NEEDS_FOR_VENTILATION', 0.02);

$sampleCaseData = new stdClass();
$estimatedDataStored = new stdClass();
$estimatedDataStored->data = new stdClass();
$estimatedDataStored->impact = new stdClass();
$estimatedDataStored->severeImpact = new stdClass();

/**
 * @function calculateCurrentlyInfected
 * @param sampleCaseData
 * @returns currentlyInfected
 * @description estimates and saves  the number of currently and severly infected people
 */

function calculateCurrentlyInfected() {
    global $sampleCaseData, $estimatedDataStored;
    // update impact
    $saveCurrentlyInfected = $sampleCaseData->reportedCases * NORMAL_INFECTION_GROWTH_RATE;
    $estimatedDataStored->impact->currentlyInfected = $saveCurrentlyInfected;
    // update severeImpact
    $saveSeverelyInfected = $sampleCaseData->reportedCases * SEVERE_INFECTION_GROWTH_RATE;
    $estimatedDataStored->severeImpact->currentlyInfected = $saveSeverelyInfected;
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
      global $sampleCaseData, $estimatedDataStored;
      
    $INFECTION_RATE_PER_PERIOD = calculateInfectionRatesPerPeriod($sampleCaseData->timeToElapse, $sampleCaseData->periodType);
    // update impact
    $saveNormalSpreadRate = $estimatedDataStored->impact->currentlyInfected * $INFECTION_RATE_PER_PERIOD;
    $estimatedDataStored->impact->infectionsByRequestedTime = $saveNormalSpreadRate;
    // update severeImpact
    $saveSevereSpreadRate = $estimatedDataStored->severeImpact->currentlyInfected * $INFECTION_RATE_PER_PERIOD;
    $estimatedDataStored->severeImpact->infectionsByRequestedTime = $saveSevereSpreadRate;
}

/**
 * @function calculateSevereCases
 * @param sampleCaseData
 * @returns severeCasesByRequestedTime
 * @description This is the estimated number of severe positive cases that will require hospitalization to recover.
 */

function calculateSevereCases() {
      global $sampleCaseData, $estimatedDataStored;
    // update impact
    $estimatedNormalPositive = $estimatedDataStored->impact->infectionsByRequestedTime * PERCENTAGE_POSITIVE_CASES;
    $estimatedDataStored->impact->severeCasesByRequestedTime = $estimatedNormalPositive;

    // update severeImpact
    $estimatedSeverePositive = $estimatedDataStored->severeImpact->infectionsByRequestedTime * PERCENTAGE_POSITIVE_CASES;
    $estimatedDataStored->severeImpact->severeCasesByRequestedTime = $estimatedSeverePositive;
}

/**
 * @function caclulatHospitalBedsAvailability
 * @param sampleCaseData
 * @returns hospitalBedsByRequestedTime
 * @description This is the estimated a 35% bed availability in hospitals for severe COVID-19 positive patients.
 */

function caclulateHospitalBedsAvailability() {
      global $sampleCaseData, $estimatedDataStored;
    // update impact
    $HOSPITAL_BEDS_AVAILABLE = $sampleCaseData->totalHospitalBeds * PERCENTAGE_HOSPITAL_BED_AVAILABILITY;
    $saveNormalHospitalBedAvailable = intval($HOSPITAL_BEDS_AVAILABLE - $estimatedDataStored->impact->severeCasesByRequestedTime);
    $estimatedDataStored->impact->hospitalBedsByRequestedTime = $saveNormalHospitalBedAvailable;
    // update severeImpact
    $saveSevereHospitalBedAvailable = intval($HOSPITAL_BEDS_AVAILABLE - $estimatedDataStored->severeImpact->severeCasesByRequestedTime);
    $estimatedDataStored->severeImpact->hospitalBedsByRequestedTime = $saveSevereHospitalBedAvailable;
}

/**
 * @function calculationICURequirement
 * @param sampleCaseData
 * @returns casesForICUByRequestedTime
 * @description This is the estimated number of severe positive cases that will require ICU care.
 */

function calculationICURequirement() {
      global $sampleCaseData, $estimatedDataStored;
    // update impact
    $saveNormalCasesNeadingICUCare = intval($estimatedDataStored->impact->infectionsByRequestedTime * PERCENTAGE_CASES_NEEDS_FOR_ICU_CARE);
    $estimatedDataStored->impact->casesForICUByRequestedTime = $saveNormalCasesNeadingICUCare;
    // update severeImpact
    $saveSeverCasesNeadingICUCare = intval($estimatedDataStored->severeImpact->infectionsByRequestedTime * PERCENTAGE_CASES_NEEDS_FOR_ICU_CARE);
    $estimatedDataStored->severeImpact->casesForICUByRequestedTime = $saveSeverCasesNeadingICUCare;
}

/**
 * @function calculateVentilatorsRequired
 * @param sampleCaseData
 * @returns casesForVentilatorsByRequestedTime
 * @description This is the estimated number of severe positive cases that will require ventilators
 */

function calculateVentilatorsRequired() {
      global $sampleCaseData, $estimatedDataStored;
    // update impact
    $saveNormalCasesNeedingVentilators = intval($estimatedDataStored->impact->infectionsByRequestedTime * PERCENTAGE_CASES_NEEDS_FOR_VENTILATION);
    $estimatedDataStored->impact->casesForVentilatorsByRequestedTime = $saveNormalCasesNeedingVentilators;
    // update severeImpact
    $saveSeverCasesNeedingVentilators = intval($estimatedDataStored->severeImpact->infectionsByRequestedTime * PERCENTAGE_CASES_NEEDS_FOR_VENTILATION);
    $estimatedDataStored->severeImpact->casesForVentilatorsByRequestedTime = $saveSeverCasesNeedingVentilators;
}

/**
 * @function calculateCostImapctOnEconomy
 * @param sampleCaseData
 * @returns dollarsInFlight
 * @description estimate how much money the economy is likely to lose over the said period.
 */

function calculateCostImapctOnEconomy() {
      global $sampleCaseData, $estimatedDataStored;
    $PERIOD_IN_FOCUS = calculateIAndReturnPeriods($sampleCaseData->timeToElapse, $sampleCaseData->periodType);
    $MAJORITIY_WORKING_POPULATION = $sampleCaseData->region->avgDailyIncomePopulation;
    $DAILY_EARNINGS = $sampleCaseData->region->avgDailyIncomeInUSD;

    // update impact
    $saveNormalDollarsInFlight = intval(($estimatedDataStored->impact->infectionsByRequestedTime * $MAJORITIY_WORKING_POPULATION * $DAILY_EARNINGS) / $PERIOD_IN_FOCUS);
    $estimatedDataStored->impact->dollarsInFlight = $saveNormalDollarsInFlight;
    // update severeImpact
    $saveSeverDollarInFlight = intval(($estimatedDataStored->severeImpact->infectionsByRequestedTime * $MAJORITIY_WORKING_POPULATION * $DAILY_EARNINGS) / $PERIOD_IN_FOCUS);
    $estimatedDataStored->severeImpact->dollarsInFlight = $saveSeverDollarInFlight;
}

function initCovidEstimator($data) {
      global $sampleCaseData, $estimatedDataStored;
    if ($data !== null) {
        // initialize variables
        $sampleCaseData = json_decode($data);
        $estimatedDataStored->data = json_decode($data);

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
    return json_encode($estimatedDataStored);
  }
   // throw new Error('Error in data Entry');
}


function covid19ImpactEstimator($data)
{
  return initCovidEstimator($data);
}