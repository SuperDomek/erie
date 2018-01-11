<?php

/**
 * @file Affiliations.inc.php
 *
 * Copyright (c) 2016 Dominik Bláha
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_user
 *
 * @brief Set up affiliations and addresses.
 * Returns false if the affiliation and addresses keys are not consistent
 */

//$Id$

import('i18n.PKPLocale');

class Affiliations {
  // to store affiliations
  private $affiliations = array();

  // to store suffixes for affiliations
  private $suffixes = array();

  // to store addresses
  private $addresses = array();

  // to store CompanyIds
  private $companyIds = array();

  // to store VATRegNos
  private $VATRegNos = array();

  function __construct() {
    $this->initArrays();

    return $this->checkConsistency();
  }

  /**
   * Cycles through the addresses array and checks that there is a corresponding
   * key in affiliations array
   * Returns false if not consistent
   */
  private function checkConsistency() {
    foreach ($this->addresses as $key => $value){
      if(!array_key_exists($key, $this->affiliations['cs_CZ']) OR !array_key_exists($key, $this->affiliations['en_US'])){
        error_log("Error while creating Affiliation object:The address and affiliation keys are not consistent.");
        return false;
      }
    }
    return true;
  }

  /**
   * Returns the array with localized affiliations or the in the locale given
   * @param $locale String locale for which to return affiliations
   * @return array String
   */
  function getAffiliations($locale = null){
    if(!empty($this->affiliations)){
      if(!$locale){
        $locale = AppLocale::getLocale();
      }
      return $this->affiliations[$locale];
    }
  }

  /**
   * Returns the suffix for given affiliation_select
   * @return String
   */
  function getSuffixes(){
    if(!empty($this->suffixes)){
      return $this->suffixes;
    }
  }

  /**
   * Returns the array with addresses
   * @return array String
   */
  function getAddresses(){
    if(!empty($this->addresses)){
      return $this->addresses;
    }
  }

  /**
   * Returns the array with Company Ids
   * @return array String
   */
  function getCompanyIds(){
    if(!empty($this->companyIds)){
      return $this->companyIds;
    }
    else error_log("Error getting companyIds.");
  }

  /**
   * Returns the array with VAT Registration Numbers
   * @return array String
   */
  function getVatRegNos(){
    if(!empty($this->VATRegNos)){
      return $this->VATRegNos;
    }
    else error_log("Error getting VAT Reg. numbers.");
  }

  /**
   * Initializes the arrays with addresses, affiliations, company IDs and VAT Reg. nums
   *
   */
  function initArrays(){
    $this->affiliations['cs_CZ'] = array(
      'PEF' => array(
        "KET" => "Katedra ekonomických teorií",
        "KE" => "Katedra ekonomiky",
        "KHV" => "Katedra humanitních věd",
        "KII" => "Katedra informačního inženýrství",
        "KIT" => "Katedra informačních technologií",
        "KJ" => "Katedra jazyků",
        "KOF" => "Katedra obchodu a financí",
        "KPR" => "Katedra práva",
        "KPS" => "Katedra psychologie",
        "KR" => "Katedra řízení",
        "KS" => "Katedra statistiky",
        "KSI" => "Katedra systémového inženýrství"
      ),
      'FAPPZ' => array(
        "KAB" => "Katedra agroekologie a biometeorologie",
        "KAVR" => "Katedra agroenvironmentální chemie a výživy rostlin",
        "KBFR" => "Katedra botaniky a fyziologie rostlin",
        "KGS" => "Katedra genetiky a šlechtění",
        "KCH" => "Katedra chemie",
        "KKZP" => "Katedra kvality zemědělských produktů",
        "KMVD" => "Katedra mikrobiologie, výživy a dietetiky",
        "KOZE" => "Katedra obecné zootechniky a etologie",
        "KOR" => "Katedra ochrany rostlin",
        "KPOP" => "Katedra pedologie a ochrany půd",
        "KPT" => "Katedra pícninářství a trávníkářství",
        "KRV" => "Katedra rostlinné výroby",
        "KSZ" => "Katedra speciální zootechniky",
        "KVD" => "Katedra veterinárních disciplín",
        "KVZ" => "Katedra vodních zdrojů",
        "KZ" => "Katedra zahradnictví",
        "KZKA" => "Katedra zahradní a krajinné architektury",
        "KZR" => "Katedra zoologie a rybářství"
      ),
      'TF' => array(
        "KEA" => "Katedra elektrotechniky a automatizace",
        "KF" => "Katedra fyziky",
        "KJSS" => "Katedra jakosti a spolehlivosti strojů",
        "KM" => "Katedra matematiky",
        "KMST" => "Katedra materiálu a strojírenské technologie",
        "KMS" => "Katedra mechaniky a strojnictví",
        "KTZS" => "Katedra technologických zařízení staveb",
        "KVPD" => "Katedra vozidel a pozemní dopravy",
        "KVS" => "Katedra využití strojů",
        "KZS" => "Katedra zemědělských strojů"
      ),
      'FZP' => array(
        "KAE" => "Katedra aplikované ekologie",
        "KAGUP" => "Katedra aplikované geoinformatiky a územního plánování",
        "KBUK" => "Katedra biotechnických úprav krajiny",
        "KEKO" => "Katedra ekologie",
        "KGEV" => "Katedra geoenvironmentálních věd",
        "KVHEM" => "Katedra vodního hospodářství a environmentálního modelování"
      ),
      'FLD' => array(
        "KDVK" => "Katedra dřevěných výrobků a konstrukcí",
        "KEL" => "Katedra ekologie lesa",
        "KGFLD" => "Katedra genetiky a fyziologie lesních dřevin",
        "KHUL" => "Katedra hospodářské úpravy lesů",
        "KERLH" => "Katedra lesnické a dřevařské ekonomiky",
        "KLT" => "Katedra lesnických technologií a staveb",
        "KMLZ" => "Katedra myslivosti a lesnické zoologie",
        "KOLE" => "Katedra ochrany lesa a entomologie",
        "KPL" => "Katedra pěstování lesů",
        "KZZD" => "Katedra základního zpracování dřeva"
      ),
      'FTZ' => array(
        "KCZPTS" => "Katedra chovu zvířat a potravinářství v tropech",
        "KER" => "Katedra ekonomiky a rozvoje",
        "KTSPA" => "Katedra tropických plodin a agrolesnictví",
        "KUT" => "Katedra udržitelných technologií"
      ),
      'IVP' => array(
        "KCVPS" => "Katedra celoživotního vzdělávání a podpory studia",
        "KPE" => "Katedra pedagogiky",
        "KPPR" => "Katedra profesního a personálního rozvoje"
      ),
      'else' => 'Jiné...'
    );
    $this->affiliations['en_US'] = array(
      'PEF' => array(
        "KET" => "Department of Economic Theories",
        "KE" => "Department of Economics",
        "KHV" => "Department of Humanities",
        "KII" => "Department of Information Engineering",
        "KIT" => "Department of Information Technologies",
        "KJ" => "Department of Languages",
        "KOF" => "Department of Trade and Accounting",
        "KPR" => "Department of Law",
        "KPS" => "Department of Psychology",
        "KR" => "Department of Management",
        "KS" => "Department of Statistics",
        "KSI" => "Department of Systems Engineering"
      ),
      'FAPPZ' => array(
        "KAB" => "Department of Agroecology and Biometeorology",
        "KAVR" => "Department of Agrienvironmental Chemistry and Plant Nutrition",
        "KBFR" => "Department of Botany and Plant Physiology",
        "KGS" => "Department of Genetics and Breeding",
        "KCH" => "Department of Chemistry",
        "KKZP" => "Department of Quality of Agricultural Products",
        "KMVD" => "Department of Microbiology, Nutrition and Dietetics",
        "KOZE" => "Department of Husbandry and Ethology of Animals",
        "KOR" => "Department of Crop Protection",
        "KPOP" => "Department of Soil Science and Soil Protection",
        "KPT" => "Department of Forage Crops and Grassland Management",
        "KRV" => "Department of Crop Production",
        "KSZ" => "Department of Animal Husbandry",
        "KVD" => "Department of Veterinary Sciences",
        "KVZ" => "Department of Water Resources",
        "KZ" => "Department of Gardening",
        "KZKA" => "Department of Horticulture",
        "KZR" => "Department of Zoology and Fisheries"
      ),
      'TF' => array(
        "KEA" => "Department of Electrical Engineering and Automation",
        "KF" => "Department of Physics",
        "KJSS" => "Department for Quality and Dependability of Machines",
        "KM" => "Department of Mathematics",
        "KMST" => "Department of Material Science and Manufacturing Technology",
        "KMS" => "Department of Mechanical Engineering",
        "KTZS" => "Department of Technological Equipment of Buildings",
        "KVPD" => "Department of Vehicles and Ground Transport",
        "KVS" => "Department of Machinery Utilization",
        "KZS" => "Department of Agricultural Machines"
      ),
      'FZP' => array(
        "KAE" => "Department of Applied Ecology",
        "KAGUP" => "Department of Applied Geoinformatics and Spatial Planning",
        "KBUK" => "Department of Land Use and Improvement",
        "KEKO" => "Department of Ecology",
        "KGEV" => "Department of Environmental Geosciences",
        "KVHEM" => "Department of Water Resources and Environmental Modeling"
      ),
      'FLD' => array(
        "KDVK" => "Department of Wood Products and Wood Constructions",
        "KEL" => "Department of Forest Ecology",
        "KGFLD" => "Department of Genetics and Physiology of Forest Trees",
        "KHUL" => "Department of Forest Management",
        "KERLH" => "Department of Forestry and Wood Economics",
        "KLT" => "Department of Forest Technologies and Construction",
        "KMLZ" => "Department of game management and wildlife biology",
        "KOLE" => "Department of Forest Protection and Entomology",
        "KPL" => "Department of Silviculture",
        "KZZD" => "Department of Wood Processing"
      ),
      'FTZ' => array(
        "KCZPTS" => "Department of Animal Science and Food Processing",
        "KER" => "Department of Economics and Development",
        "KTSPA" => "Department of Crop Sciences and Agroforestry",
        "KUT" => "Department of Sustainable Technologies"
      ),
      'IVP' => array(
        "KCVPS" => "Department of Lifelong Learning and Study Support",
        "KPE" => "Department of Pedagogy",
        "KPPR" => "Department of Professional and Personal Development"
      ),
      'else' => "Specify bellow..."
    );
/*
    $locale = AppLocale::getLocale();

    switch($locale) {
      case "cs_CZ":
        $this->affiliations["PEF"] = array(
          "KET" => "Katedra ekonomických teorií",
          "KE" => "Katedra ekonomiky",
          "KHV" => "Katedra humanitních věd",
          "KII" => "Katedra informačního inženýrství",
          "KIT" => "Katedra informačních technologií",
          "KJ" => "Katedra jazyků",
          "KOF" => "Katedra obchodu a financí",
          "KPR" => "Katedra práva",
          "KPS" => "Katedra psychologie",
          "KR" => "Katedra řízení",
          "KS" => "Katedra statistiky",
          "KSI" => "Katedra systémového inženýrství"
        );
        $this->affiliations["FAPPZ"] = array(
          "KAB" => "Katedra agroekologie a biometeorologie",
          "KAVR" => "Katedra agroenvironmentální chemie a výživy rostlin",
          "KBFR" => "Katedra botaniky a fyziologie rostlin",
          "KGS" => "Katedra genetiky a šlechtění",
          "KCH" => "Katedra chemie",
          "KKZP" => "Katedra kvality zemědělských produktů",
          "KMVD" => "Katedra mikrobiologie, výživy a dietetiky",
          "KOZE" => "Katedra obecné zootechniky a etologie",
          "KOR" => "Katedra ochrany rostlin",
          "KPOP" => "Katedra pedologie a ochrany půd",
          "KPT" => "Katedra pícninářství a trávníkářství",
          "KRV" => "Katedra rostlinné výroby",
          "KSZ" => "Katedra speciální zootechniky",
          "KVD" => "Katedra veterinárních disciplín",
          "KVZ" => "Katedra vodních zdrojů",
          "KZ" => "Katedra zahradnictví",
          "KZKA" => "Katedra zahradní a krajinné architektury",
          "KZR" => "Katedra zoologie a rybářství"
        );
        $this->affiliations["TF"] = array(
          "KEA" => "Katedra elektrotechniky a automatizace",
          "KF" => "Katedra fyziky",
          "KJSS" => "Katedra jakosti a spolehlivosti strojů",
          "KM" => "Katedra matematiky",
          "KMST" => "Katedra materiálu a strojírenské technologie",
          "KMS" => "Katedra mechaniky a strojnictví",
          "KTZS" => "Katedra technologických zařízení staveb",
          "KVPD" => "Katedra vozidel a pozemní dopravy",
          "KVS" => "Katedra využití strojů",
          "KZS" => "Katedra zemědělských strojů"
        );
        $this->affiliations["FZP"] = array(
          "KAE" => "Katedra aplikované ekologie",
          "KAGUP" => "Katedra aplikované geoinformatiky a územního plánování",
          "KBUK" => "Katedra biotechnických úprav krajiny",
          "KEKO" => "Katedra ekologie",
          "KGEV" => "Katedra geoenvironmentálních věd",
          "KVHEM" => "Katedra vodního hospodářství a environmentálního modelování"
        );
        $this->affiliations["FLD"] = array(
          "KDVK" => "Katedra dřevěných výrobků a konstrukcí",
          "KEL" => "Katedra ekologie lesa",
          "KGFLD" => "Katedra genetiky a fyziologie lesních dřevin",
          "KHUL" => "Katedra hospodářské úpravy lesů",
          "KERLH" => "Katedra lesnické a dřevařské ekonomiky",
          "KLT" => "Katedra lesnických technologií a staveb",
          "KMLZ" => "Katedra myslivosti a lesnické zoologie",
          "KOLE" => "Katedra ochrany lesa a entomologie",
          "KPL" => "Katedra pěstování lesů",
          "KZZD" => "Katedra základního zpracování dřeva"
        );
        $this->affiliations["FTZ"] = array(
          "KCZPTS" => "Katedra chovu zvířat a potravinářství v tropech",
          "KER" => "Katedra ekonomiky a rozvoje",
          "KTSPA" => "Katedra tropických plodin a agrolesnictví",
          "KUT" => "Katedra udržitelných technologií"
        );
        $this->affiliations["IVP"] = array(
          "KCVPS" => "Katedra celoživotního vzdělávání a podpory studia",
          "KPE" => "Katedra pedagogiky",
          "KPPR" => "Katedra profesního a personálního rozvoje"
        );
        $this->affiliations["else"] = "Jiné...";
        break;
      case "en_US":
        $this->affiliations["PEF"] = array(
          "KET" => "Department of Economic Theories",
          "KE" => "Department of Economics",
          "KHV" => "Department of Humanities",
          "KII" => "Department of Information Engineering",
          "KIT" => "Department of Information Technologies",
          "KJ" => "Department of Languages",
          "KOF" => "Department of Trade and Accounting",
          "KPR" => "Department of Law",
          "KPS" => "Department of Psychology",
          "KR" => "Department of Management",
          "KS" => "Department of Statistics",
          "KSI" => "Department of Systems Engineering"
        );
        $this->affiliations["FAPPZ"] = array(
          "KAB" => "Department of Agroecology and Biometeorology",
          "KAVR" => "Department of Agrienvironmental Chemistry and Plant Nutrition",
          "KBFR" => "Department of Botany and Plant Physiology",
          "KGS" => "Department of Genetics and Breeding",
          "KCH" => "Department of Chemistry",
          "KKZP" => "Department of Quality of Agricultural Products",
          "KMVD" => "Department of Microbiology, Nutrition and Dietetics",
          "KOZE" => "Department of Husbandry and Ethology of Animals",
          "KOR" => "Department of Crop Protection",
          "KPOP" => "Department of Soil Science and Soil Protection",
          "KPT" => "Department of Forage Crops and Grassland Management",
          "KRV" => "Department of Crop Production",
          "KSZ" => "Department of Animal Husbandry",
          "KVD" => "Department of Veterinary Sciences",
          "KVZ" => "Department of Water Resources",
          "KZ" => "Department of Gardening",
          "KZKA" => "Department of Horticulture",
          "KZR" => "Department of Zoology and Fisheries"
        );
        $this->affiliations["TF"] = array(
          "KEA" => "Department of Electrical Engineering and Automation",
          "KF" => "Department of Physics",
          "KJSS" => "Department for Quality and Dependability of Machines",
          "KM" => "Department of Mathematics",
          "KMST" => "Department of Material Science and Manufacturing Technology",
          "KMS" => "Department of Mechanical Engineering",
          "KTZS" => "Department of Technological Equipment of Buildings",
          "KVPD" => "Department of Vehicles and Ground Transport",
          "KVS" => "Department of Machinery Utilization",
          "KZS" => "Department of Agricultural Machines"
        );
        $this->affiliations["FZP"] = array(
          "KAE" => "Department of Applied Ecology",
          "KAGUP" => "Department of Applied Geoinformatics and Spatial Planning",
          "KBUK" => "Department of Land Use and Improvement",
          "KEKO" => "Department of Ecology",
          "KGEV" => "Department of Environmental Geosciences",
          "KVHEM" => "Department of Water Resources and Environmental Modeling"
        );
        $this->affiliations["FLD"] = array(
          "KDVK" => "Department of Wood Products and Wood Constructions",
          "KEL" => "Department of Forest Ecology",
          "KGFLD" => "Department of Genetics and Physiology of Forest Trees",
          "KHUL" => "Department of Forest Management",
          "KERLH" => "Department of Forestry and Wood Economics",
          "KLT" => "Department of Forest Technologies and Construction",
          "KMLZ" => "Department of game management and wildlife biology",
          "KOLE" => "Department of Forest Protection and Entomology",
          "KPL" => "Department of Silviculture",
          "KZZD" => "Department of Wood Processing"
        );
        $this->affiliations["FTZ"] = array(
          "KCZPTS" => "Department of Animal Science and Food Processing",
          "KER" => "Department of Economics and Development",
          "KTSPA" => "Department of Crop Sciences and Agroforestry",
          "KUT" => "Department of Sustainable Technologies"
        );
        $this->affiliations["IVP"] = array(
          "KCVPS" => "Department of Lifelong Learning and Study Support",
          "KPE" => "Department of Pedagogy",
          "KPPR" => "Department of Professional and Personal Development"
        );
        $this->affiliations["else"] = "Specify bellow...";
        break;
    }*/

    // Initialization of suffixes to affiliations (would be nice to fill the keys using foreach from affiliations)
    $keysPef = array('KET', 'KE', 'KHV', 'KII', 'KIT', 'KJ', 'KOF', 'KPR', 'KPS', 'KR', 'KS', 'KSI');
    $keysFappz = array('KAB', 'KAVR', 'KBFR', 'KGS', 'KCH', 'KKZP', 'KMVD', 'KOZE', 'KOR', 'KPOP', 'KPT', 'KRV', 'KSZ', 'KVD', 'KVZ', 'KZ', 'KZKA', 'KZR');
    $keysTf = array('KEA', 'KF', 'KJSS', 'KM', 'KMST', 'KMS', 'KTZS', 'KVPD', 'KVS', 'KZS');
    $keysFzp = array('KAE', 'KAGUP', 'KBUK', 'KEKO', 'KGEV', 'KVHEM');
    $keysFld = array('KDVK', 'KEL', 'KGFLD', 'KHUL', 'KERLH', 'KLT', 'KMLZ', 'KOLE', 'KPL', 'KZZD');
    $keysFtz = array('KCZPTS', 'KER', 'KTSPA', 'KUT');
    $keysIvp = array('KCVPS', 'KPE', 'KPPR');
    $keyElse = array('else');
    $this->suffixes += array_fill_keys($keysPef, ', Faculty of Economics and Management, Czech University of Life Sciences');
    $this->suffixes += array_fill_keys($keysFappz, ', Faculty of Agrobiology, Food and Natural Resources, Czech University of Life Sciences');
    $this->suffixes += array_fill_keys($keysTf, ', Faculty of Engineering, Czech University of Life Sciences');
    $this->suffixes += array_fill_keys($keysFzp, ', Faculty of Environmental Sciences, Czech University of Life Sciences');
    $this->suffixes += array_fill_keys($keysFld, ', Faculty of Forestry and Wood Sciences, Czech University of Life Sciences');
    $this->suffixes += array_fill_keys($keysFtz, ', Faculty of Tropical AgriSciences, Czech University of Life Sciences');
    $this->suffixes += array_fill_keys($keysIvp, ', Institute of Education and Communication, Czech University of Life Sciences');
    $this->suffixes += array_fill_keys($keyElse, '');

    // Initialization of addresses
    // The array keys need to be consistent with affiliations keys
    $this->addresses["PEF"] = "Provozně ekonomická fakulta\\nČeská zemědělská univerzita v Praze\\nKamýcká 129\\n165 21 Praha 6 - Suchdol";
    $this->addresses["TF"] = "Technická fakulta\\nČeská zemědělská univerzita v Praze\\nKamýcká 129\\n165 21 Praha 6 - Suchdol";
    $this->addresses["FAPPZ"] = "Fakulta agrobiologie, potravinových a přírodních zdrojů\\nČeská zemědělská univerzita v Praze\\nKamýcká 129\\n165 00 Praha 6 - Suchdol";
    $this->addresses["FZP"] = "Fakulta životního prostředí\\nČeská zemědělská univerzita v Praze\\nKamýcká 129\\n165 21 Praha 6 - Suchdol";
    $this->addresses["FLD"] = "Fakulta lesnická a dřevařská\\nČeská zemědělská univerzita v Praze\\nKamýcká 129\\n165 00 Praha 6 - Suchdol";
    $this->addresses["FTZ"] = "Fakulta tropického zemědělství\\nČeská zemědělská univerzita v Praze\\nKamýcká 129\\n165 00 Praha 6 - Suchdol";
    $this->addresses["IVP"] = "Institut vzdělávání a poradenství\\nČeská zemědělská univerzita v Praze\\nKamýcká 129\\n165 00 Praha 6 - Suchdol";

    // Initialization of CompanyIds
    $this->companyIds["CULS"] = "60460709";
    // Initialization of VATRegNos
    $this->VATRegNos["CULS"] = "CZ60460709";
  }
}



?>
