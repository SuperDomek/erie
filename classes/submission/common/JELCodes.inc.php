<?php

/**
 * @defgroup submission
 */

/**
 * @file JELCodes.inc.php
 *
 * Copyright (c) 2017 Dominik Blaha
 *  Inspiration from John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JELCodes
 * @ingroup submission
 *
 * @brief Class for manipulating JEL codes.
 */

//$Id$

import('db.DBConnection');

class JELCodes {

	// Database connection object
	private $_dataSource;

	// JEL classification
	private $JELClassification = array();

  /**
  * Constructor.
  * Initalization the connection to database.
  *
  */
  function __construct(){
    if (!isset($dataSource)) {
			$this->_dataSource =& DBConnection::getConn();
		} else {
			$this->_dataSource = $dataSource;
		}
		$this->init();
  }

	/**
	*	Returns an associative array with values for the select drop down list
	*	@return array
	*/
	function getClassification(){
		if(!empty($this->JELClassification)) return $this->JELClassification;
	}

	/**
	*	Returns a keyword for a given code
	* @return string
	*/
	function getKeyword($code){
		foreach ($this->JELClassification as $key => $optgroup) {
			foreach ($optgroup as $JELCode => $keyword) {
				if($JELCode === $code){
					return $keyword;
				}
			}
		}
		error_log("Error while getting JEL keyword: Key not found.");
		return false;
	}

  /**
	 * Returns an associative array with matching rows from the db for the paper
	 * @param $paper object
   * @return array
	 */
	function getCodes($paperID = null) {
    $conn = $this->_dataSource;
    if (isset($paperID)){
      $codes = $conn->Execute('SELECT * FROM paper_jel_codes WHERE paper_id = ?', array($paperID));
      if (!$codes) {
         print $conn->ErrorMsg();
       }
      else {
        $temp = array();
        //print "<pre>";
        while (!$codes->EOF) {
            //print $codes->fields[0].' '.$codes->fields[1]. ' ' . $codes->fields[2] . '<BR>';
            $temp[] = $codes->fields;
            $codes->MoveNext();
        }    $codes->Close(); # optional
        //print "</pre>";
        return $temp;
      }
    }
    else {
      error_log("Error while getting JEL codes: PaperID not set up.");
      return NULL;
    }

	}

	/**
	*	Sets up JEL code for given paper
	*	@param $paper_id ID of the paper to set up JEL code
	*	@param $code Code of the JEL keyword
	*	@param $keyword Classification keyword
	*/
	function setCode($paper_id, $key, $keyword){
		$conn = $this->_dataSource;
		if(isset($paper_id)){
			if(isset($key)){
				if(isset($keyword)){
					$sql = "INSERT INTO `paper_jel_codes`(`paper_id`, `code`, `code_keyword`) VALUES (?,?,?)";
					$err = $conn->Execute($sql, array($paper_id, $key, $keyword));
					if($err === false){
						error_log("Error while inserting JEL code: " . $conn->ErrorMsg());
					}
				}
				else {
					error_log("Error while setting up JEL code: keyword not set up.");
					return false;
				}
			}
			else {
				error_log("Error while setting up JEL code: key not set up.");
				return false;
			}
		}
		else {
			error_log("Error while setting up JEL code: Paper ID not set up.");
			return false;
		}
	}

	/**
	*	Sets up the array with JEL Classification for the select in form
	*/
	function init(){
		$classification = array();
		$temp["A General Economics and Teaching"] = array();
		$temp["&nbsp;&nbsp;General Economics"] = array(
			"A10" => "A10 General",
			"A11" => "A11 Role of Economics / Role of Economists / Market for Economists",
			"A12" => "A12 Relation of Economics to Other Disciplines",
			"A13" => "A13 Relation of Economics to Social Values",
			"A14" => "A14 Sociology of Economics",
			"A19" => "A19 Other"
		);
		$temp["&nbsp;&nbsp;Economic Education and Teaching of Economics"] = array(
			"A20" => "A20 General",
			"A21" => "A21 Pre-college",
			"A22" => "A22 Undergraduate",
			"A23" => "A23 Graduate",
			"A29" => "A29 Other"
		);
		$temp["&nbsp;&nbsp;Collective Works"] = array(
			"A30" => "A30 General",
			"A31" => "A31 Collected Writings of Individuals",
			"A32" => "A32 Collective Volumes",
			"A33" => "A33 Handbooks",
			"A39" => "A39 Other"
		);
		$temp["B History of Economic Thought, Methodology, and Heterodox Approaches"] = array(
			"B00" => "B00 General"
		);
		$temp["&nbsp;&nbsp;History of Economic Thought through 1925"] = array(
			"B10" => "B10 General",
			"B11" => "B11 Preclassical (Ancient, Medieval, Mercantilist, Physiocratic)",
			"B12" => "B12 Classical (includes Adam Smith)",
			"B13" => "B13 Neoclassical through 1925 (Austrian, Marshallian, Walrasian, Stockholm School)",
			"B14" => "B14 Socialist / Marxist",
			"B15" => "B15 Historical / Institutional / Evolutionary",
			"B16" => "B16 Quantitative and Mathematical",
			"B19" => "B19 Other"
		);
		$temp["&nbsp;&nbsp;History of Economic Thought since 1925"] = array(
			"B20" => "B20 General",
			"B21" => "B21 Microeconomics",
			"B22" => "B22 Macroeconomics",
			"B23" => "B23 Econometrics / Quantitative and Mathematical Studies",
			"B24" => "B24 Socialist / Marxist / Sraffian",
			"B25" => "B25 Historical / Institutional / Evolutionary / Austrian",
			"B26" => "B26 Financial Economics",
			"B29" => "B29 Other"
		);
		$temp["&nbsp;&nbsp;History of Economic Thought: Individuals"] = array(
			"B30" => "B30 General",
			"B31" => "B31 Individuals",
			"B32" => "B32 Obituaries"
		);
		$temp["&nbsp;&nbsp;Economic Methodology"] = array(
			"B40" => "B40 General",
			"B41" => "B41 Economic Methodology",
			"B49" => "B49 Other"
		);
		$temp["&nbsp;&nbsp;Current Heterodox Approaches"] = array(
			"B50" => "B50 General",
			"B51" => "B51 Socialist / Marxian / Sraffian",
			"B52" => "B52 Institutional / Evolutionary",
			"B53" => "B53 Austrian",
			"B54" => "B54 Feminist Economics",
			"B59" => "B59 Other"
		);
		$temp["C Mathematical and Quantitative Methods"] = array(
			"C00" => "C00 General",
			"C01" => "C01 Econometrics",
			"C02" => "C02 Mathematical Methods"
		);
		$temp["&nbsp;&nbsp;Econometric and Statistical Methods and Methodology: General"] = array(
			"C10" => "C10 General",
			"C11" => "C11 Bayesian Analysis: General",
			"C12" => "C12 Hypothesis Testing: General",
			"C13" => "C13 Estimation: General",
			"C14" => "C14 Semiparametric and Nonparametric Methods: General",
			"C15" => "C15 Statistical Simulation Methods: General",
			"C18" => "C18 Methodological Issues: General",
			"C19" => "C19 Other"
		);
		$temp["&nbsp;&nbsp;Single Equation Models / Single Variables"] = array(
			"C20" => "C20 General",
			"C21" => "C21 Cross-Sectional Models / Spatial Models / Treatment Effect Models / Quantile Regressions",
			"C22" => "C22 Time-Series Models / Dynamic Quantile Regressions / Dynamic Treatment Effect Models / Diffusion Processes",
			"C23" => "C23 Panel Data Models / Spatio-temporal Models",
			"C24" => "C24 Truncated and Censored Models / Switching Regression Models / Threshold Regression Models",
			"C25" => "C25 Discrete Regression and Qualitative Choice Models / Discrete Regressors / Proportions / Probabilities",
			"C26" => "C26 Instrumental Variables (IV) Estimation",
			"C29" => "C29 Other"
		);
		$temp["&nbsp;&nbsp;Multiple or Simultaneous Equation Models / Multiple Variables"] = array(
			"C30" => "C30 General",
			"C31" => "C31 Cross-Sectional Models / Spatial Models / Treatment Effect Models / Quantile Regressions / Social Interaction Models",
			"C32" => "C32 Time-Series Models / Dynamic Quantile Regressions / Dynamic Treatment Effect Models / Diffusion Processes / State Space Models",
			"C33" => "C33 Panel Data Models / Spatio-temporal Models",
			"C34" => "C34 Truncated and Censored Models / Switching Regression Models",
			"C35" => "C35 Discrete Regression and Qualitative Choice Models / Discrete Regressors / Proportions",
			"C36" => "C36 Instrumental Variables (IV) Estimation",
			"C38" => "C38 Classification Methods / Cluster Analysis / Principal Components / Factor Models",
			"C39" => "C39 Other"
		);
		$temp["&nbsp;&nbsp;Econometric and Statistical Methods: Special Topics"] = array(
			"C40" => "C40 General",
			"C41" => "C41 Duration Analysis / Optimal Timing Strategies",
			"C43" => "C43 Index Numbers and Aggregation",
			"C44" => "C44 Operations Research / Statistical Decision Theory ",
			"C45" => "C45 Neural Networks and Related Topics",
			"C46" => "C46 Specific Distributions / Specific Statistics",
			"C49" => "C49 Other"
		);
		$temp["&nbsp;&nbsp;Econometric Modeling"] = array(
			"C50" => "C50 General",
			"C51" => "C51 Model Construction and Estimation",
			"C52" => "C52 Model Evaluation, Validation, and Selection",
			"C53" => "C53 Forecasting and Prediction Methods / Simulation Methods",
			"C54" => "C54 Quantitative Policy Modeling",
			"C55" => "C55 Large Data Sets: Modeling and Analysis",
			"C57" => "C57 Econometrics of Games and Auctions",
			"C58" => "C58 Financial Econometrics",
			"C59" => "C59 Other"
		);
		$temp["&nbsp;&nbsp;Mathematical Methods / Programming Models / Mathematical and Simulation Modeling"] = array(
			"C60" => "C60 General",
			"C61" => "C61 Optimization Techniques / Programming Models / Dynamic Analysis",
			"C62" => "C62 Existence and Stability Conditions of Equilibrium",
			"C63" => "C63 Computational Techniques / Simulation Modeling",
			"C65" => "C65 Miscellaneous Mathematical Tools",
			"C67" => "C67 Input-Output Models",
			"C68" => "C68 Computable General Equilibrium Models",
			"C69" => "C69 Other"
		);
		$temp["&nbsp;&nbsp;Game Theory and Bargaining Theory"] = array(
			"C70" => "C70 General",
			"C71" => "C71 Cooperative Games",
			"C72" => "C72 Noncooperative Games",
			"C73" => "C73 Stochastic and Dynamic Games / Evolutionary Games / Repeated Games",
			"C78" => "C78 Bargaining Theory / Matching Theory",
			"C79" => "C79 Other"
		);
		$temp["&nbsp;&nbsp;Data Collection and Data Estimation Methodology / Computer Programs"] = array(
			"C80" => "C80 General",
			"C81" => "C81 Methodology for Collecting, Estimating, and Organizing Microeconomic Data / Data Access",
			"C82" => "C82 Methodology for Collecting, Estimating, and Organizing Macroeconomic Data / Data Access",
			"C83" => "C83 Survey Methods / Sampling Methods",
			"C87" => "C87 Econometric Software",
			"C88" => "C88 Other Computer Software",
			"C89" => "C89 Other"
		);
		$temp["&nbsp;&nbsp;Design of Experiments"] = array(
			"C90" => "C90 General",
			"C91" => "C91 Laboratory, Individual Behavior",
			"C92" => "C92 Laboratory, Group Behavior",
			"C93" => "C93 Field Experiments",
			"C99" => "C99 Other"
		);
		$temp["D Microeconomics"] = array(
			"D00" => "D00 General",
			"D01" => "D01 Microeconomic Behavior: Underlying Principles",
			"D02" => "D02 Institutions: Design, Formation, Operations, and Impact",
			"D03" => "D03 Behavioral Microeconomics: Underlying Principles",
			"D04" => "D04 Microeconomic Policy: Formulation, Implementation, and Evaluation"
		);
		$temp["&nbsp;&nbsp;Household Behavior and Family Economics"] = array(
			"D10" => "D10 General",
			"D11" => "D11 Consumer Economics: Theory",
			"D12" => "D12 Consumer Economics: Empirical Analysis",
			"D13" => "D13 Household Production and Intrahousehold Allocation",
			"D14" => "D14 Household Saving; Personal Finance",
			"D18" => "D18 Consumer Protection",
			"D19" => "D19 Other"
		);
		$temp["&nbsp;&nbsp;Production and Organizations"] = array(
			"D20" => "D20 General",
			"D21" => "D21 Firm Behavior: Theory",
			"D22" => "D22 Firm Behavior: Empirical Analysis",
			"D23" => "D23 Organizational Behavior / Transaction Costs / Property Rights",
			"D24" => "D24 Production / Cost / Capital / Capital, Total Factor, and Multifactor Productivity / Capacity",
			"D29" => "D29 Other"
		);
		$temp["&nbsp;&nbsp;Distribution"] = array(
			"D30" => "D30 General",
			"D31" => "D31 Personal Income, Wealth, and Their Distributions",
			"D33" => "D33 Factor Income Distribution",
			"D39" => "D39 Other"
		);
		$temp["&nbsp;&nbsp;Market Structure, Pricing, and Design"] = array(
			"D40" => "D40 General",
			"D41" => "D41 Perfect Competition",
			"D42" => "D42 Monopoly",
			"D43" => "D43 Oligopoly and Other Forms of Market Imperfection",
			"D44" => "D44 Auctions",
			"D45" => "D45 Rationing / Licensing",
			"D46" => "D46 Value Theory",
			"D47" => "D47 Market Design",
			"D49" => "D49 Other"
		);
		$temp["&nbsp;&nbsp;General Equilibrium and Disequilibrium"] = array(
			"D50" => "D50 General",
			"D51" => "D51 Exchange and Production Economies",
			"D52" => "D52 Incomplete Markets",
			"D53" => "D53 Financial Markets",
			"D57" => "D57 Input-Output Tables and Analysis",
			"D58" => "D58 Computable and Other Applied General Equilibrium Models",
			"D59" => "D59 Other"
		);
		$temp["&nbsp;&nbsp;Welfare Economics"] = array(
			"D60" => "D60 General",
			"D61" => "D61 Allocative Efficiency / Cost-Benefit Analysis",
			"D62" => "D62 Externalities",
			"D63" => "D63 Equity, Justice, Inequality, and Other Normative Criteria and Measurement",
			"D64" => "D64 Altruism / Philanthropy / Intergenerational Transfers",
			"D69" => "D69 Other"
		);
		$temp["&nbsp;&nbsp;Analysis of Collective Decision-Making"] = array(
			"D70" => "D70 General",
			"D71" => "D71 Social Choice / Clubs / Committees / Associations",
			"D72" => "D72 Political Processes: Rent-Seeking, Lobbying, Elections, Legislatures, and Voting Behavior",
			"D73" => "D73 Bureaucracy / Administrative Processes in Public Organizations / Corruption",
			"D74" => "D74 Conflict / Conflict Resolution / Alliances / Revolutions",
			"D78" => "D78 Positive Analysis of Policy Formulation and Implementation",
			"D79" => "D79 Other"
		);
		$temp["&nbsp;&nbsp;Information, Knowledge, and Uncertainty"] = array(
			"D80" => "D80 General",
			"D81" => "D81 Criteria for Decision-Making under Risk and Uncertainty",
			"D82" => "D82 Asymmetric and Private Information / Mechanism Design",
			"D83" => "D83 Search / Learning / Information and Knowledge / Communication / Belief / Unawareness",
			"D84" => "D84 Expectations / Speculations",
			"D85" => "D85 Network Formation and Analysis: Theory",
			"D86" => "D86 Economics of Contract: Theory",
			"D87" => "D87 Neuroeconomics",
			"D89" => "D89 Other"
		);
		$temp["&nbsp;&nbsp;Intertemporal Choice"] = array(
			"D90" => "D90 General",
			"D91" => "D91 Intertemporal Household Choice / Life Cycle Models and Saving",
			"D92" => "D92 Intertemporal Firm Choice, Investment, Capacity, and Financing",
			"D99" => "D99 Other"
		);
		$temp["E Macroeconomics and Monetary Economics"] = array(
			"E00" => "E00 General",
			"E01" => "E01 Measurement and Data on National Income and Product Accounts and Wealth / Environmental Accounts",
			"E02" => "E02 Institutions and the Macroeconomy",
			"E03" => "E03 Behavioral Macroeconomics"
		);
		$temp["&nbsp;&nbsp;General Aggregative Models"] = array(
			"E10" => "E10 General",
			"E11" => "E11 Marxian / Sraffian / Kaleckian",
			"E12" => "E12 Keynes / Keynesian / Post-Keynesian",
			"E14" => "E14 Austrian / Evolutionary / Institutional ",
			"E13" => "E13 Neoclassical",
			"E16" => "E16 Social Accounting Matrix",
			"E17" => "E17 Forecasting and Simulation: Models and Applications",
			"E19" => "E19 Other"
		);
		$temp["&nbsp;&nbsp;Consumption, Saving, Production, Investment, Labor Markets, and Informal Economy"] = array(
			"E20" => "E20 General",
			"E21" => "E21 Consumption / Saving / Wealth",
			"E22" => "E22 Investment / Capital / Intangible Capital / Capacity",
			"E23" => "E23 Production",
			"E24" => "E24 Employment / Unemployment / Wages / Intergenerational Income Distribution / Aggregate Human Capital / Aggregate Labor Productivity",
			"E25" => "E25 Aggregate Factor Income Distribution",
			"E26" => "E26 Informal Economy / Underground Economy",
			"E27" => "E27 Forecasting and Simulation: Models and Applications",
			"E29" => "E29 Other"
		);
		$temp["&nbsp;&nbsp;Prices, Business Fluctuations, and Cycles"] = array(
			"E30" => "E30 General",
			"E31" => "E31 Price Level / Inflation / Deflation",
			"E32" => "E32 Business Fluctuations / Cycles",
			"E37" => "E37 Forecasting and Simulation: Models and Applications",
			"E39" => "E39 Other"
		);
		$temp["&nbsp;&nbsp;Money and Interest Rates"] = array(
			"E40" => "E40 General",
			"E41" => "E41 Demand for Money",
			"E42" => "E42 Monetary Systems / Standards / Regimes / Government and the Monetary System / Payment Systems",
			"E43" => "E43 Interest Rates: Determination, Term Structure, and Effects ",
			"E44" => "E44 Financial Markets and the Macroeconomy",
			"E47" => "E47 Forecasting and Simulation: Models and Applications",
			"E49" => "E49 Other"
		);
		$temp["&nbsp;&nbsp;Monetary Policy, Central Banking, and the Supply of Money and Credit"] = array(
			"E50" => "E50 General",
			"E51" => "E51 Money Supply / Credit / Money Multipliers",
			"E52" => "E52 Monetary Policy",
			"E58" => "E58 Central Banks and Their Policies",
			"E59" => "E59 Other"
		);
		$temp["&nbsp;&nbsp;Macroeconomic Policy, Macroeconomic Aspects of Public Finance, and General Outlook"] = array(
			"E60" => "E60 General",
			"E61" => "E61 Policy  Objectives / Policy Designs and Consistency / Policy Coordination",
			"E62" => "E62 Fiscal Policy",
			"E63" => "E63 Comparative or Joint Analysis of Fiscal and Monetary Policy / Stabilization / Treasury Policy",
			"E64" => "E64 Incomes Policy / Price Policy",
			"E65" => "E65 Studies of Particular Policy Episodes",
			"E66" => "E66 General Outlook and Conditions",
			"E69" => "E69 Other"
		);
		$temp["F International Economics"] = array(
			"F00" => "F00 General",
			"F01" => "F01 Global Outlook",
			"F02" => "F02 International Economic Order and Integration"
		);
		$temp["&nbsp;&nbsp;Trade"] = array(
			"F10" => "F10 General",
			"F11" => "F11 Neoclassical Models of Trade",
			"F12" => "F12 Models of Trade with Imperfect Competition and Scale Economies / Fragmentation",
			"F13" => "F13 Trade Policy / International Trade Organizations",
			"F14" => "F14 Empirical Studies of Trade",
			"F15" => "F15 Economic Integration",
			"F16" => "F16 Trade and Labor Market Interactions",
			"F17" => "F17 Trade Forecasting and Simulation",
			"F18" => "F18 Trade and Environment",
			"F19" => "F19 Other"
		);
		$temp["&nbsp;&nbsp;International Factor Movements and International Business"] = array(
			"F20" => "F20 General",
			"F21" => "F21 International Investment / Long-Term Capital Movements",
			"F22" => "F22 International Migration",
			"F23" => "F23 Multinational Firms / International Business",
			"F24" => "F24 Remittances",
			"F29" => "F29 Other"
		);
		$temp["&nbsp;&nbsp;International Finance"] = array(
			"F30" => "F30 General",
			"F31" => "F31 Foreign Exchange",
			"F32" => "F32 Current Account Adjustment / Short-Term Capital Movements",
			"F33" => "F33 International Monetary Arrangements and Institutions",
			"F34" => "F34 International Lending and Debt Problems",
			"F35" => "F35 Foreign Aid",
			"F36" => "F36 Financial Aspects of Economic Integration",
			"F37" => "F37 International Finance Forecasting and Simulation: Models and Applications",
			"F38" => "F38 International Financial Policy: Financial Transactions Tax; Capital Controls",
			"F39" => "F39 Other"
		);
		$temp["&nbsp;&nbsp;Macroeconomic Aspects of International Trade and Finance"] = array(
			"F40" => "F40 General",
			"F41" => "F41 Open Economy Macroeconomics",
			"F42" => "F42 International Policy Coordination and Transmission",
			"F43" => "F43 Economic Growth of Open Economies",
			"F44" => "F44 International Business Cycles",
			"F45" => "F45 Macroeconomic Issues of Monetary Unions",
			"F47" => "F47 Forecasting and Simulation: Models and Applications",
			"F49" => "F49 Other"
		);
		$temp["&nbsp;&nbsp;International Relations, National Security, and International Political Economy"] = array(
			"F50" => "F50 General",
			"F51" => "F51 International Conflicts / Negotiations / Sanctions",
			"F52" => "F52 National Security / Economic Nationalism",
			"F53" => "F53 International Agreements and Observance / International Organizations",
			"F54" => "F54 Colonialism / Imperialism / Postcolonialism",
			"F55" => "F55 International Institutional Arrangements",
			"F59" => "F59 Other"
		);
		$temp["&nbsp;&nbsp;Economic Impacts of Globalization"] = array(
			"F60" => "F60 General",
			"F61" => "F61 Microeconomic Impacts",
			"F62" => "F62 Macroeconomic Impacts",
			"F63" => "F63 Economic Development",
			"F64" => "F64 Environment",
			"F65" => "F65 Finance",
			"F66" => "F66 Labor",
			"F68" => "F68 Policy",
			"F69" => "F69 Other"
		);
		$temp["G Financial Economics"] = array(
			"G00" => "G00 General",
			"G01" => "G01 Financial Crises",
			"G02" => "G02 Behavioral Finance: Underlying Principles"
		);
		$temp["&nbsp;&nbsp;General Financial Markets"] = array(
			"G10" => "G10 General",
			"G11" => "G11 Portfolio Choice / Investment Decisions",
			"G12" => "G12 Asset Pricing / Trading Volume / Bond Interest Rates",
			"G13" => "G13 Contingent Pricing / Futures Pricing",
			"G14" => "G14 Information and Market Efficiency / Event Studies / Insider Trading",
			"G15" => "G15 International Financial Markets",
			"G17" => "G17 Financial Forecasting and Simulation",
			"G18" => "G18 Government Policy and Regulation",
			"G19" => "G19 Other"
		);
		$temp["&nbsp;&nbsp;Financial Institutions and Services"] = array(
			"G20" => "G20 General",
			"G21" => "G21 Banks / Depository Institutions / Micro Finance Institutions / Mortgages",
			"G22" => "G22 Insurance / Insurance Companies / Actuarial Studies",
			"G23" => "G23 Non-bank Financial Institutions / Financial Instruments / Institutional Investors",
			"G24" => "G24 Investment Banking / Venture Capital / Brokerage / Ratings and Ratings Agencies",
			"G28" => "G28 Government Policy and Regulation",
			"G29" => "G29 Other"
		);
		$temp["&nbsp;&nbsp;Corporate Finance and Governance"] = array(
			"G30" => "G30 General",
			"G31" => "G31 Capital Budgeting / Fixed Investment and Inventory Studies / Capacity",
			"G32" => "G32 Financing Policy / Financial Risk and Risk Management / Capital and Ownership Structure / Value of Firms / Goodwill",
			"G33" => "G33 Bankruptcy / Liquidation",
			"G34" => "G34 Mergers / Acquisitions / Restructuring / Corporate Governance",
			"G35" => "G35 Payout Policy",
			"G38" => "G38 Government Policy and Regulation",
			"G39" => "G39 Other"
		);
		$temp["H Public Economics"] = array(
			"H00" => "H00 General"
		);
		$temp["&nbsp;&nbsp;Structure and Scope of Government"] = array(
			"H10" => "H10 General",
			"H11" => "H11 Structure, Scope, and Performance of Government",
			"H12" => "H12 Crisis Management",
			"H13" => "H13 Economics of Eminent Domain / Expropriation / Nationalization",
			"H19" => "H19 Other"
		);
		$temp["&nbsp;&nbsp;Taxation, Subsidies, and Revenue"] = array(
			"H20" => "H20 General",
			"H21" => "H21 Efficiency / Optimal Taxation",
			"H22" => "H22 Incidence",
			"H23" => "H23 Externalities / Redistributive Effects / Environmental Taxes and Subsidies",
			"H24" => "H24 Personal Income and Other Nonbusiness Taxes and Subsidies",
			"H25" => "H25 Business Taxes and Subsidies",
			"H26" => "H26 Tax Evasion and Avoidance",
			"H27" => "H27 Other Sources of Revenue",
			"H29" => "H29 Other"
		);
		$temp["&nbsp;&nbsp;Fiscal Policies and Behavior of Economic Agents"] = array(
			"H30" => "H30 General",
			"H31" => "H31 Household",
			"H32" => "H32 Firm",
			"H39" => "H39 Other"
		);
		$temp["&nbsp;&nbsp;Publicly Provided Goods"] = array(
			"H40" => "H40 General",
			"H41" => "H41 Public Goods",
			"H42" => "H42 Publicly Provided Private Goods",
			"H43" => "H43 Project Evaluation / Social Discount Rate",
			"H44" => "H44 Publicly Provided Goods: Mixed Markets",
			"H49" => "H49 Other"
		);
		$temp["&nbsp;&nbsp;National Government Expenditures and Related Policies"] = array(
			"H50" => "H50 General",
			"H51" => "H51 Government Expenditures and Health",
			"H52" => "H52 Government Expenditures and Education",
			"H53" => "H53 Government Expenditures and Welfare Programs",
			"H54" => "H54 Infrastructures / Other Public Investment and Capital Stock",
			"H55" => "H55 Social Security and Public Pensions",
			"H56" => "H56 National Security and War",
			"H57" => "H57 Procurement",
			"H59" => "H59 Other"
		);
		$temp["&nbsp;&nbsp;National Budget, Deficit, and Debt"] = array(
			"H60" => "H60 General",
			"H61" => "H61 Budget / Budget Systems",
			"H62" => "H62 Deficit / Surplus",
			"H63" => "H63 Debt / Debt Management / Sovereign Debt",
			"H68" => "H68 Forecasts of Budgets, Deficits, and Debt",
			"H69" => "H69 Other"
		);
		$temp["&nbsp;&nbsp;State and Local Government / Intergovernmental Relations"] = array(
			"H70" => "H70 General",
			"H71" => "H71 State and Local Taxation, Subsidies, and Revenue",
			"H72" => "H72 State and Local Budget and Expenditures",
			"H73" => "H73 Interjurisdictional Differentials and Their Effects",
			"H74" => "H74 State and Local Borrowing",
			"H75" => "H75 State and Local Government: Health / Education / Welfare / Public Pensions",
			"H76" => "H76 State and Local Government: Other Expenditure Categories",
			"H77" => "H77 Intergovernmental Relations / Federalism / Secession",
			"H79" => "H79 Other"
		);
		$temp["&nbsp;&nbsp;Miscellaneous Issues"] = array(
			"H80" => "H80 General",
			"H81" => "H81 Governmental Loans / Loan Guarantees / Credits / Grants / Bailouts",
			"H82" => "H82 Governmental Property",
			"H83" => "H83 Public Administration / Public Sector Accounting and Audits",
			"H84" => "H84 Disaster Aid",
			"H87" => "H87 International Fiscal Issues / International Public Goods",
			"H89" => "H89 Other"
		);
		$temp["I Health, Education, and Welfare"] = array(
			"I00" => "I00 General"
		);
		$temp["&nbsp;&nbsp;Health"] = array(
			"I10" => "I10 General",
			"I11" => "I11 Analysis of Health Care Markets",
			"I12" => "I12 Health Behavior",
			"I13" => "I13 Health Insurance, Public and Private",
			"I14" => "I14 Health and Inequality",
			"I15" => "I15 Health and Economic Development",
			"I18" => "I18 Government Policy / Regulation / Public Health",
			"I19" => "I19 Other"
		);
		$temp["&nbsp;&nbsp;Education and Research Institutions"] = array(
			"I20" => "I20 General",
			"I21" => "I21 Analysis of Education",
			"I22" => "I22 Educational Finance / Financial Aid",
			"I23" => "I23 Higher Education / Research Institutions",
			"I24" => "I24 Education and Inequality",
			"I25" => "I25 Education and Economic Development",
			"I26" => "I26 Returns to Education",
			"I28" => "I28 Government Policy",
			"I29" => "I29 Other"
		);
		$temp["&nbsp;&nbsp;Welfare, Well-Being, and Poverty"] = array(
			"I30" => "I30 General",
			"I31" => "I31 General Welfare, Well-Being  ",
			"I32" => "I32 Measurement and Analysis of Poverty",
			"I38" => "I38 Government Policy / Provision and Effects of Welfare Programs",
			"I39" => "I39 Other"
		);
		$temp["J Labor and Demographic Economics"] = array(
			"J00" => "J00 General",
			"J01" => "J01 Labor Economics: General",
			"J08" => "J08 Labor Economics Policies"
		);
		$temp["&nbsp;&nbsp;Demographic Economics"] = array(
			"J10" => "J10 General",
			"J11" => "J11 Demographic Trends, Macroeconomic Effects, and Forecasts",
			"J12" => "J12 Marriage / Marital Dissolution / Family Structure / Domestic Abuse",
			"J13" => "J13 Fertility / Family Planning / Child Care / Children / Youth",
			"J14" => "J14 Economics of the Elderly / Economics of the Handicapped / Non-Labor Market Discrimination",
			"J15" => "J15 Economics of Minorities, Races, Indigenous Peoples, and Immigrants / Non-labor Discrimination",
			"J16" => "J16 Economics of Gender / Non-labor Discrimination",
			"J17" => "J17 Value of Life / Forgone Income",
			"J18" => "J18 Public Policy",
			"J19" => "J19 Other"
		);
		$temp["&nbsp;&nbsp;Demand and Supply of Labor"] = array(
			"J20" => "J20 General",
			"J21" => "J21 Labor Force and Employment, Size, and Structure",
			"J22" => "J22 Time Allocation and Labor Supply",
			"J23" => "J23 Labor Demand",
			"J24" => "J24 Human Capital / Skills / Occupational Choice / Labor Productivity",
			"J26" => "J26 Retirement / Retirement Policies",
			"J28" => "J28 Safety / Job Satisfaction / Related Public Policy",
			"J29" => "J29 Other"
		);
		$temp["&nbsp;&nbsp;Wages, Compensation, and Labor Costs"] = array(
			"J30" => "J30 General",
			"J31" => "J31 Wage Level and Structure / Wage Differentials",
			"J32" => "J32 Nonwage Labor Costs and Benefits / Retirement Plans / Private Pensions",
			"J33" => "J33 Compensation Packages / Payment Methods",
			"J38" => "J38 Public Policy",
			"J39" => "J39 Other"
		);
		$temp["&nbsp;&nbsp;Particular Labor Markets"] = array(
			"J40" => "J40 General",
			"J41" => "J41 Labor Contracts",
			"J42" => "J42 Monopsony / Segmented Labor Markets",
			"J43" => "J43 Agricultural Labor Markets",
			"J44" => "J44 Professional Labor Markets / Occupational Licensing",
			"J45" => "J45 Public Sector Labor Markets",
			"J46" => "J46 Informal Labor Markets",
			"J47" => "J47 Coercive Labor Markets",
			"J48" => "J48 Public Policy",
			"J49" => "J49 Other"
		);
		$temp["&nbsp;&nbsp;Labor-Management Relations, Trade Unions, and Collective Bargaining"] = array(
			"J50" => "J50 General",
			"J51" => "J51 Trade Unions: Objectives, Structure, and Effects",
			"J52" => "J52 Dispute Resolution:  Strikes, Arbitration, and Mediation / Collective Bargaining",
			"J53" => "J53 Labor-Management Relations / Industrial Jurisprudence",
			"J54" => "J54 Producer Cooperatives / Labor Managed Firms / Employee Ownership",
			"J58" => "J58 Public Policy",
			"J59" => "J59 Other"
		);
		$temp["&nbsp;&nbsp;Mobility, Unemployment, Vacancies, and Immigrant Workers"] = array(
			"J60" => "J60 General",
			"J61" => "J61 Geographic Labor Mobility / Immigrant Workers",
			"J62" => "J62 Job, Occupational, and Intergenerational Mobility",
			"J63" => "J63 Turnover / Vacancies / Layoffs",
			"J64" => "J64 Unemployment: Models, Duration, Incidence, and Job Search",
			"J65" => "J65 Unemployment Insurance / Severance Pay / Plant Closings",
			"J68" => "J68 Public Policy",
			"J69" => "J69 Other"
		);
		$temp["&nbsp;&nbsp;Labor Discrimination"] = array(
			"J70" => "J70 General",
			"J71" => "J71 Discrimination",
			"J78" => "J78 Public Policy",
			"J79" => "J79 Other"
		);
		$temp["&nbsp;&nbsp;Labor Standards: National and International"] = array(
			"J80" => "J80 General",
			"J81" => "J81 Working Conditions",
			"J82" => "J82 Labor Force Composition",
			"J83" => "J83 Workers Rights",
			"J88" => "J88 Public Policy",
			"J89" => "J89 Other"
		);
		$temp["K Law and Economics"] = array(
			"K00" => "K00 General"
		);
		$temp["&nbsp;&nbsp;Basic Areas of Law"] = array(
			"K10" => "K10 General",
			"K11" => "K11 Property Law",
			"K12" => "K12 Contract Law",
			"K13" => "K13 Tort Law and Product Liability / Forensic Economics",
			"K14" => "K14 Criminal Law",
			"K19" => "K19 Other"
		);
		$temp["&nbsp;&nbsp;Regulation and Business Law"] = array(
			"K20" => "K20 General",
			"K21" => "K21 Antitrust Law",
			"K22" => "K22 Business and Securities Law",
			"K23" => "K23 Regulated Industries and Administrative Law",
			"K29" => "K29 Other"
		);
		$temp["&nbsp;&nbsp;Other Substantive Areas of Law"] = array(
			"K30" => "K30 General",
			"K31" => "K31 Labor Law",
			"K32" => "K32 Environmental, Health, and Safety Law",
			"K33" => "K33 International Law",
			"K34" => "K34 Tax Law",
			"K35" => "K35 Personal Bankruptcy Law",
			"K36" => "K36 Family and Personal Law",
			"K37" => "K37 Immigration Law",
			"K39" => "K39 Other"
		);
		$temp["&nbsp;&nbsp;Legal Procedure, the Legal System, and Illegal Behavior"] = array(
			"K40" => "K40 General",
			"K41" => "K41 Litigation Process",
			"K42" => "K42 Illegal Behavior and the Enforcement of Law",
			"K49" => "K49 Other"
		);
		$temp["L Industrial Organization"] = array(
			"L00" => "L00 General"
		);
		$temp["&nbsp;&nbsp;Market Structure, Firm Strategy, and Market Performance"] = array(
			"L10" => "L10 General",
			"L11" => "L11 Production, Pricing, and Market Structure / Size Distribution of Firms",
			"L12" => "L12 Monopoly / Monopolization Strategies",
			"L13" => "L13 Oligopoly and Other Imperfect Markets",
			"L14" => "L14 Transactional Relationships / Contracts and Reputation / Networks",
			"L15" => "L15 Information and Product Quality / Standardization and Compatibility",
			"L16" => "L16 Industrial Organization and Macroeconomics: Industrial Structure and Structural Change / Industrial Price Indices",
			"L17" => "L17 Open Source Products and Markets",
			"L19" => "L19 Other"
		);
		$temp["&nbsp;&nbsp;Firm Objectives, Organization, and Behavior"] = array(
			"L20" => "L20 General",
			"L21" => "L21 Business Objectives of the Firm",
			"L22" => "L22 Firm Organization and Market Structure",
			"L23" => "L23 Organization of Production",
			"L24" => "L24 Contracting Out / Joint Ventures / Technology Licensing",
			"L25" => "L25 Firm Performance: Size, Diversification, and Scope",
			"L26" => "L26 Entrepreneurship",
			"L29" => "L29 Other"
		);
		$temp["&nbsp;&nbsp;Nonprofit Organizations and Public Enterprise"] = array(
			"L30" => "L30 General",
			"L31" => "L31 Nonprofit Institutions / NGOs / Social Entrepreneurship",
			"L32" => "L32 Public Enterprises / Public-Private Enterprises",
			"L33" => "L33 Comparison of Public and Private Enterprises and Nonprofit Institutions / Privatization / Contracting Out",
			"L38" => "L38 Public Policy",
			"L39" => "L39 Other"
		);
		$temp["&nbsp;&nbsp;Antitrust Issues and Policies"] = array(
			"L40" => "L40 General",
			"L41" => "L41 Monopolization / Horizontal Anticompetitive Practices",
			"L42" => "L42 Vertical Restraints / Resale Price Maintenance / Quantity Discounts",
			"L43" => "L43 Legal Monopolies and Regulation or Deregulation",
			"L44" => "L44 Antitrust Policy and Public Enterprises, Nonprofit Institutions, and Professional Organizations",
			"L49" => "L49 Other"
		);
		$temp["&nbsp;&nbsp;Regulation and Industrial Policy"] = array(
			"L50" => "L50 General",
			"L51" => "L51 Economics of Regulation",
			"L52" => "L52 Industrial Policy / Sectoral Planning Methods",
			"L53" => "L53 Enterprise Policy ",
			"L59" => "L59 Other"
		);
		$temp["&nbsp;&nbsp;Industry Studies: Manufacturing"] = array(
			"L60" => "L60 General",
			"L61" => "L61 Metals and Metal Products / Cement / Glass / Ceramics",
			"L62" => "L62 Automobiles / Other Transportation Equipment / Related Parts and Equipment",
			"L63" => "L63 Microelectronics / Computers / Communications Equipment",
			"L64" => "L64 Other Machinery / Business Equipment / Armaments",
			"L65" => "L65 Chemicals / Rubber / Drugs / Biotechnology",
			"L66" => "L66 Food / Beverages / Cosmetics / Tobacco / Wine and Spirits",
			"L67" => "L67 Other Consumer Nondurables: Clothing, Textiles, Shoes, and Leather Goods; Household Goods; Sports Equipment",
			"L68" => "L68 Appliances / Furniture / Other Consumer Durables",
			"L69" => "L69 Other"
		);
		$temp["&nbsp;&nbsp;Industry Studies: Primary Products and Construction"] = array(
			"L70" => "L70 General",
			"L71" => "L71 Mining, Extraction, and Refining: Hydrocarbon Fuels",
			"L72" => "L72 Mining, Extraction, and Refining: Other Nonrenewable Resources",
			"L73" => "L73 Forest Products",
			"L74" => "L74 Construction",
			"L78" => "L78 Government Policy",
			"L79" => "L79 Other"
		);
		$temp["&nbsp;&nbsp;Industry Studies: Services"] = array(
			"L80" => "L80 General",
			"L81" => "L81 Retail and Wholesale Trade / e-Commerce",
			"L82" => "L82 Entertainment / Media",
			"L83" => "L83 Sports / Gambling / Restaurants / Recreation / Tourism",
			"L84" => "L84 Personal, Professional, and Business Services",
			"L85" => "L85 Real Estate Services",
			"L86" => "L86 Information and Internet Services / Computer Software",
			"L87" => "L87 Postal and Delivery Services",
			"L88" => "L88 Government Policy",
			"L89" => "L89 Other"
		);
		$temp["&nbsp;&nbsp;Industry Studies: Transportation and Utilities"] = array(
			"L90" => "L90 General",
			"L91" => "L91 Transportation: General",
			"L92" => "L92 Railroads and Other Surface Transportation",
			"L93" => "L93 Air Transportation",
			"L94" => "L94 Electric Utilities",
			"L95" => "L95 Gas Utilities / Pipelines / Water Utilities",
			"L96" => "L96 Telecommunications",
			"L97" => "L97 Utilities: General",
			"L98" => "L98 Government Policy",
			"L99" => "L99 Other"
		);
		$temp["M Business Administration and Business Economics / Marketing / Accounting / Personnel Economics"] = array(
			"M00" => "M00 General"
		);
		$temp["&nbsp;&nbsp;Business Administration"] = array(
			"M10" => "M10 General",
			"M11" => "M11 Production Management",
			"M12" => "M12 Personnel Management / Executives; Executive Compensation",
			"M13" => "M13 New Firms / Startups",
			"M14" => "M14 Corporate Culture / Diversity / Social Responsibility",
			"M15" => "M15 IT Management",
			"M16" => "M16 International Business Administration",
			"M19" => "M19 Other"
		);
		$temp["&nbsp;&nbsp;Business Economics"] = array(
			"M20" => "M20 General",
			"M21" => "M21 Business Economics",
			"M29" => "M29 Other"
		);
		$temp["&nbsp;&nbsp;Marketing and Advertising"] = array(
			"M30" => "M30 General",
			"M31" => "M31 Marketing",
			"M37" => "M37 Advertising",
			"M38" => "M38 Government Policy and Regulation",
			"M39" => "M39 Other"
		);
		$temp["&nbsp;&nbsp;Accounting and Auditing"] = array(
			"M40" => "M40 General",
			"M41" => "M41 Accounting",
			"M42" => "M42 Auditing",
			"M48" => "M48 Government Policy and Regulation",
			"M49" => "M49 Other"
		);
		$temp["&nbsp;&nbsp;Personnel Economics"] = array(
			"M50" => "M50 General",
			"M51" => "M51 Firm Employment Decisions / Promotions",
			"M52" => "M52 Compensation and Compensation Methods and Their Effects",
			"M53" => "M53 Training",
			"M54" => "M54 Labor Management",
			"M55" => "M55 Labor Contracting Devices",
			"M59" => "M59 Other"
		);
		$temp["N Economic History"] = array(
			"N00" => "N00 General",
			"N01" => "N01 Development of the Discipline: Historiographical; Sources and Methods"
		);
		$temp["&nbsp;&nbsp;Macroeconomics and Monetary Economics / Industrial Structure / Growth / Fluctuations"] = array(
			"N10" => "N10 General, International, or Comparative",
			"N11" => "N11 U.S. / Canada: Pre-1913",
			"N12" => "N12 U.S. / Canada: 1913-",
			"N13" => "N13 Europe: Pre-1913",
			"N14" => "N14 Europe: 1913-",
			"N15" => "N15 Asia including Middle East",
			"N16" => "N16 Latin America / Caribbean",
			"N17" => "N17 Africa / Oceania"
		);
		$temp["&nbsp;&nbsp;Financial Markets and Institutions"] = array(
			"N20" => "N20 General, International, or Comparative",
			"N21" => "N21 U.S. / Canada: Pre-1913",
			"N22" => "N22 U.S. / Canada: 1913-",
			"N23" => "N23 Europe: Pre-1913",
			"N24" => "N24 Europe: 1913-",
			"N25" => "N25 Asia including Middle East",
			"N26" => "N26 Latin America / Caribbean",
			"N27" => "N27 Africa / Oceania"
		);
		$temp["&nbsp;&nbsp;Labor and Consumers, Demography, Education, Health, Welfare, Income, Wealth, Religion, and Philanthropy"] = array(
			"N30" => "N30 General, International, or Comparative",
			"N31" => "N31 U.S. / Canada: Pre-1913",
			"N32" => "N32 U.S. / Canada: 1913-",
			"N33" => "N33 Europe: Pre-1913",
			"N34" => "N34 Europe: 1913-",
			"N35" => "N35 Asia including Middle East",
			"N36" => "N36 Latin America / Caribbean",
			"N37" => "N37 Africa / Oceania"
		);
		$temp["&nbsp;&nbsp;Government, War, Law, International Relations, and Regulation"] = array(
			"N40" => "N40 General, International, or Comparative",
			"N41" => "N41 U.S. / Canada: Pre-1913",
			"N42" => "N42 U.S. / Canada: 1913-",
			"N43" => "N43 Europe: Pre-1913",
			"N44" => "N44 Europe: 1913-",
			"N45" => "N45 Asia including Middle East",
			"N46" => "N46 Latin America / Caribbean",
			"N47" => "N47 Africa / Oceania"
		);
		$temp["&nbsp;&nbsp;Agriculture, Natural Resources, Environment, and Extractive Industries"] = array(
			"N50" => "N50 General, International, or Comparative",
			"N51" => "N51 U.S. / Canada: Pre-1913",
			"N52" => "N52 U.S. / Canada: 1913-",
			"N53" => "N53 Europe: Pre-1913",
			"N54" => "N54 Europe: 1913-",
			"N55" => "N55 Asia including Middle East",
			"N56" => "N56 Latin America / Caribbean",
			"N57" => "N57 Africa / Oceania"
		);
		$temp["&nbsp;&nbsp;Manufacturing and Construction"] = array(
			"N60" => "N60 General, International, or Comparative",
			"N61" => "N61 U.S. / Canada: Pre-1913",
			"N62" => "N62 U.S. / Canada: 1913-",
			"N63" => "N63 Europe: Pre-1913",
			"N64" => "N64 Europe: 1913-",
			"N65" => "N65 Asia including Middle East",
			"N66" => "N66 Latin America / Caribbean",
			"N67" => "N67 Africa / Oceania"
		);
		$temp["&nbsp;&nbsp;Transport, Trade, Energy, Technology, and Other Services"] = array(
			"N70" => "N70 General, International, or Comparative",
			"N71" => "N71 U.S. / Canada: Pre-1913",
			"N72" => "N72 U.S. / Canada: 1913-",
			"N73" => "N73 Europe: Pre-1913",
			"N74" => "N74 Europe: 1913-",
			"N75" => "N75 Asia including Middle East",
			"N76" => "N76 Latin America / Caribbean",
			"N77" => "N77 Africa / Oceania"
		);
		$temp["&nbsp;&nbsp;Micro-Business History"] = array(
			"N80" => "N80 General, International, or Comparative",
			"N81" => "N81 U.S. / Canada: Pre-1913",
			"N82" => "N82 U.S. / Canada: 1913-",
			"N83" => "N83 Europe: Pre-1913",
			"N84" => "N84 Europe: 1913-",
			"N85" => "N85 Asia including Middle East",
			"N86" => "N86 Latin America / Caribbean",
			"N87" => "N87 Africa / Oceania"
		);
		$temp["&nbsp;&nbsp;Regional and Urban History"] = array(
			"N90" => "N90 General, International, or Comparative",
			"N91" => "N91 U.S. / Canada: Pre-1913",
			"N92" => "N92 U.S. / Canada: 1913-",
			"N93" => "N93 Europe: Pre-1913",
			"N94" => "N94 Europe: 1913-",
			"N95" => "N95 Asia including Middle East",
			"N96" => "N96 Latin America / Caribbean",
			"N97" => "N97 Africa / Oceania"
		);
		$temp["O Economic Development, Innovation, Technological Change, and Growth"] = array();
		$temp["&nbsp;&nbsp;Economic Development"] = array(
			"O10" => "O10 General",
			"O11" => "O11 Macroeconomic Analyses of Economic Development",
			"O12" => "O12 Microeconomic Analyses of Economic Development",
			"O13" => "O13 Agriculture / Natural Resources / Energy / Environment / Other Primary Products",
			"O14" => "O14 Industrialization / Manufacturing and Service Industries / Choice of Technology",
			"O15" => "O15 Human Resources / Human Development / Income Distribution / Migration",
			"O16" => "O16 Financial Markets / Saving and Capital Investment / Corporate Finance and Governance",
			"O17" => "O17 Formal and Informal Sectors / Shadow Economy / Institutional Arrangements",
			"O18" => "O18 Urban, Rural, Regional, and Transportation Analysis / Housing / Infrastructure",
			"O19" => "O19 International Linkages to Development / Role of International Organizations"
		);
		$temp["&nbsp;&nbsp;Development Planning and Policy"] = array(
			"O20" => "O20 General",
			"O21" => "O21 Planning Models / Planning Policy",
			"O22" => "O22 Project Analysis",
			"O23" => "O23 Fiscal and Monetary Policy in Development",
			"O24" => "O24 Trade Policy / Factor Movement Policy / Foreign Exchange Policy",
			"O25" => "O25 Industrial Policy",
			"O29" => "O29 Other"
		);
		$temp["&nbsp;&nbsp;Innovation / Research and Development / Technological Change / Intellectual Property Rights"] = array(
			"O30" => "O30 General",
			"O31" => "O31 Innovation and Invention: Processes and Incentives",
			"O32" => "O32 Management of Technological Innovation and R&D",
			"O33" => "O33 Technological Change: Choices and Consequences / Diffusion Processes",
			"O34" => "O34 Intellectual Property and Intellectual Capital",
			"O35" => "O35 Social Innovation",
			"O38" => "O38 Government Policy",
			"O39" => "O39 Other"
		);
		$temp["&nbsp;&nbsp;Economic Growth and Aggregate Productivity"] = array(
			"O40" => "O40 General",
			"O41" => "O41 One, Two, and Multisector Growth Models",
			"O42" => "O42 Monetary Growth Models",
			"O43" => "O43 Institutions and Growth",
			"O44" => "O44 Environment and Growth",
			"O47" => "O47 Empirical Studies of Economic Growth / Aggregate Productivity / Cross-Country Output Convergence",
			"O49" => "O49 Other"
		);
		$temp["&nbsp;&nbsp;Economywide Country Studies"] = array(
			"O50" => "O50 General",
			"O51" => "O51 U.S. / Canada",
			"O52" => "O52 Europe",
			"O53" => "O53 Asia including Middle East",
			"O54" => "O54 Latin America / Caribbean",
			"O55" => "O55 Africa",
			"O56" => "O56 Oceania",
			"O57" => "O57 Comparative Studies of Countries"
		);
		$temp["P Economic Systems"] = array(
			"P00" => "P00 General"
		);
		$temp["&nbsp;&nbsp;Capitalist Systems"] = array(
			"P10" => "P10 General",
			"P11" => "P11 Planning, Coordination, and Reform",
			"P12" => "P12 Capitalist Enterprises",
			"P13" => "P13 Cooperative Enterprises",
			"P14" => "P14 Property Rights",
			"P16" => "P16 Political Economy",
			"P17" => "P17 Performance and Prospects",
			"P18" => "P18 Energy / Environment",
			"P19" => "P19 Other"
		);
		$temp["&nbsp;&nbsp;Socialist Systems and Transitional Economies"] = array(
			"P20" => "P20 General",
			"P21" => "P21 Planning, Coordination, and Reform",
			"P22" => "P22 Prices",
			"P23" => "P23 Factor and Product Markets / Industry Studies / Population",
			"P24" => "P24 National Income, Product, and Expenditure / Money / Inflation",
			"P25" => "P25 Urban, Rural, and Regional Economics",
			"P26" => "P26 Political Economy / Property Rights",
			"P27" => "P27 Performance and Prospects",
			"P28" => "P28 Natural Resources / Energy / Environment",
			"P29" => "P29 Other"
		);
		$temp["&nbsp;&nbsp;Socialist Institutions and Their Transitions"] = array(
			"P30" => "P30 General",
			"P31" => "P31 Socialist Enterprises and Their Transitions",
			"P32" => "P32 Collectives / Communes / Agriculture",
			"P33" => "P33 International Trade, Finance, Investment, Relations, and Aid",
			"P34" => "P34 Financial Economics",
			"P35" => "P35 Public Economics",
			"P36" => "P36 Consumer Economics / Health / Education and Training / Welfare, Income, Wealth, and Poverty",
			"P37" => "P37 Legal Institutions / Illegal Behavior",
			"P39" => "P39 Other"
		);
		$temp["&nbsp;&nbsp;Other Economic Systems"] = array(
			"P40" => "P40 General",
			"P41" => "P41 Planning, Coordination, and Reform",
			"P42" => "P42 Productive Enterprises / Factor and Product Markets / Prices / Population",
			"P43" => "P43 Public Economics / Financial Economics",
			"P44" => "P44 National Income, Product, and Expenditure / Money / Inflation",
			"P45" => "P45 International Trade, Finance, Investment, and Aid",
			"P46" => "P46 Consumer Economics / Health / Education and Training / Welfare, Income, Wealth, and Poverty",
			"P47" => "P47 Performance and Prospects",
			"P48" => "P48 Political Economy / Legal Institutions / Property Rights / Natural Resources / Energy / Environment / Regional Studies",
			"P49" => "P49 Other"
		);
		$temp["&nbsp;&nbsp;Comparative Economic Systems"] = array(
			"P50" => "P50 General",
			"P51" => "P51 Comparative Analysis of Economic Systems",
			"P52" => "P52 Comparative Studies of Particular Economies",
			"P59" => "P59 Other"
		);
		$temp["Q Agricultural and Natural Resource Economics / Environmental and Ecological Economics"] = array(
			"Q00" => "Q00 General",
			"Q01" => "Q01 Sustainable Development",
			"Q02" => "Q02 Commodity Markets"
		);
		$temp["&nbsp;&nbsp;Agriculture"] = array(
			"Q10" => "Q10 General",
			"Q11" => "Q11 Aggregate Supply and Demand Analysis / Prices",
			"Q12" => "Q12 Micro Analysis of Farm Firms, Farm Households, and Farm Input Markets",
			"Q13" => "Q13 Agricultural Markets and Marketing / Cooperatives / Agribusiness",
			"Q14" => "Q14 Agricultural Finance",
			"Q15" => "Q15 Land Ownership and Tenure / Land Reform / Land Use / Irrigation / Agriculture and Environment",
			"Q16" => "Q16 R&D / Agricultural Technology / Biofuels / Agricultural Extension Services",
			"Q17" => "Q17 Agriculture in International Trade",
			"Q18" => "Q18 Agricultural Policy / Food Policy",
			"Q19" => "Q19 Other"
		);
		$temp["&nbsp;&nbsp;Renewable Resources and Conservation"] = array(
			"Q20" => "Q20 General",
			"Q21" => "Q21 Demand and Supply / Prices",
			"Q22" => "Q22 Fishery / Aquaculture",
			"Q23" => "Q23 Forestry",
			"Q24" => "Q24 Land",
			"Q25" => "Q25 Water",
			"Q26" => "Q26 Recreational Aspects of Natural Resources",
			"Q27" => "Q27 Issues in International Trade",
			"Q28" => "Q28 Government Policy",
			"Q29" => "Q29 Other"
		);
		$temp["&nbsp;&nbsp;Nonrenewable Resources and Conservation"] = array(
			"Q30" => "Q30 General",
			"Q31" => "Q31 Demand and Supply / Prices",
			"Q32" => "Q32 Exhaustible Resources and Economic Development",
			"Q33" => "Q33 Resource Booms",
			"Q34" => "Q34 Natural Resources and Domestic and International Conflicts",
			"Q35" => "Q35 Hydrocarbon Resources",
			"Q37" => "Q37 Issues in International Trade",
			"Q38" => "Q38 Government Policy",
			"Q39" => "Q39 Other"
		);
		$temp["&nbsp;&nbsp;Energy"] = array(
			"Q40" => "Q40 General",
			"Q41" => "Q41 Demand and Supply / Prices",
			"Q42" => "Q42 Alternative Energy Sources",
			"Q43" => "Q43 Energy and the Macroeconomy",
			"Q47" => "Q47 Energy Forecasting",
			"Q48" => "Q48 Government Policy",
			"Q49" => "Q49 Other"
		);
		$temp["&nbsp;&nbsp;Environmental Economics"] = array(
			"Q50" => "Q50 General",
			"Q51" => "Q51 Valuation of Environmental Effects",
			"Q52" => "Q52 Pollution Control Adoption and Costs / Distributional Effects / Employment Effects",
			"Q53" => "Q53 Air Pollution / Water Pollution / Noise / Hazardous Waste / Solid Waste / Recycling",
			"Q54" => "Q54 Climate / Natural Disasters and Their Management / Global Warming",
			"Q55" => "Q55 Technological Innovation",
			"Q56" => "Q56 Environment and Development / Environment and Trade / Sustainability / Environmental Accounts and Accounting / Environmental Equity / Population Growth",
			"Q57" => "Q57 Ecological Economics: Ecosystem Services / Biodiversity Conservation / Bioeconomics / Industrial Ecology",
			"Q58" => "Q58 Government Policy",
			"Q59" => "Q59 Other"
		);
		$temp["R Urban, Rural, Regional, Real Estate, and Transportation Economics"] = array(
			"R00" => "R00 General"
		);
		$temp["&nbsp;&nbsp;General Regional Economics"] = array(
			"R10" => "R10 General",
			"R11" => "R11 Regional Economic Activity: Growth, Development, Environmental Issues, and Changes",
			"R12" => "R12 Size and Spatial Distributions of Regional Economic Activity",
			"R13" => "R13 General Equilibrium and Welfare Economic Analysis of Regional Economies",
			"R14" => "R14 Land Use Patterns",
			"R15" => "R15 Econometric and Input-Output Models / Other Models",
			"R19" => "R19 Other"
		);
		$temp["&nbsp;&nbsp;Household Analysis"] = array(
			"R20" => "R20 General",
			"R21" => "R21 Housing Demand",
			"R22" => "R22 Other Demand",
			"R23" => "R23 Regional Migration / Regional Labor Markets / Population / Neighborhood Characteristics",
			"R28" => "R28 Government Policy",
			"R29" => "R29 Other"
		);
		$temp["&nbsp;&nbsp;Real Estate Markets, Spatial Production Analysis, and Firm Location"] = array(
			"R30" => "R30 General",
			"R31" => "R31 Housing Supply and Markets",
			"R32" => "R32 Other Spatial Production and Pricing Analysis",
			"R33" => "R33 Nonagricultural and Nonresidential Real Estate Markets",
			"R38" => "R38 Government Policy",
			"R39" => "R39 Other"
		);
		$temp["&nbsp;&nbsp;Transportation Economics"] = array(
			"R40" => "R40 General",
			"R41" => "R41 Transportation: Demand, Supply, and Congestion / Travel Time / Safety and Accidents / Transportation Noise",
			"R42" => "R42 Government and Private Investment Analysis / Road Maintenance / Transportation Planning",
			"R48" => "R48 Government Pricing and Policy",
			"R49" => "R49 Other"
		);
		$temp["&nbsp;&nbsp;Regional Government Analysis"] = array(
			"R50" => "R50 General",
			"R51" => "R51 Finance in Urban and Rural Economies",
			"R52" => "R52 Land Use and Other Regulations",
			"R53" => "R53 Public Facility Location Analysis / Public Investment and Capital Stock",
			"R58" => "R58 Regional Development Planning and Policy",
			"R59" => "R59 Other"
		);
		$temp["Y Miscellaneous Categories"] = array();
		$temp["&nbsp;&nbsp;Data: Tables and Charts"] = array(
			"Y10" => "Y10 Data: Tables and Charts "
		);
		$temp["&nbsp;&nbsp;Introductory Material"] = array(
			"Y20" => "Y20 Introductory Material"
		);
		$temp["&nbsp;&nbsp;Book Reviews (unclassified)"] = array(
			"Y30" => "Y30 Book Reviews (unclassified)"
		);
		$temp["&nbsp;&nbsp;Dissertations (unclassified)"] = array(
			"Y40" => "Y40 Dissertations (unclassified)"
		);
		$temp["&nbsp;&nbsp;Further Reading (unclassified)"] = array(
			"Y50" => "Y50 Further Reading (unclassified)"
		);
		$temp["&nbsp;&nbsp;Excerpts"] = array(
			"Y60" => "Y60 Excerpts"
		);
		$temp["&nbsp;&nbsp;No Author General Discussions"] = array(
			"Y70" => "Y70 No Author General Discussions"
		);
		$temp["&nbsp;&nbsp;Related Disciplines"] = array(
			"Y80" => "Y80 Related Disciplines"
		);
		$temp["&nbsp;&nbsp;Other"] = array(
			"Y90" => "Y90 Other",
			"Y91" => "Y91 Pictures and Maps",
			"Y92" => "Y92 Novels, Self-Help Books, etc."
		);
		$temp["Z Other Special Topics"] = array(
			"Z00" => "Z00 General"
		);
		$temp["&nbsp;&nbsp;Cultural Economics / Economic Sociology / Economic Anthropology"] = array(
			"Z10" => "Z10 General",
			"Z11" => "Z11 Economics of the Arts and Literature",
			"Z12" => "Z12 Religion",
			"Z13" => "Z13 Economic Sociology / Economic Anthropology / Social and Economic Stratification ",
			"Z18" => "Z18 Public Policy",
			"Z19" => "Z19 Other"
		);
		$temp["&nbsp;&nbsp;Sports Economics"] = array(
			"Z20" => "Z20 General",
			"Z21" => "Z21 Industry Studies",
			"Z22" => "Z22 Labor Issues",
			"Z23" => "Z23 Finance",
			"Z28" => "Z28 Policy",
			"Z29" => "Z29 Other"
		);
		$temp["&nbsp;&nbsp;Tourism Economics"] = array(
			"Z30" => "Z30 General",
			"Z31" => "Z31 Industry Studies",
			"Z32" => "Z32 Tourism and Development ",
			"Z33" => "Z33 Marketing and Finance ",
			"Z38" => "Z38 Policy ",
			"Z39" => "Z39 Other"
		);
		$this->JELClassification = $temp;
	}

}

?>
