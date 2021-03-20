<?php

namespace common\models\aifin;

use Yii;
use backend\models\IncomeForm;

class Plan extends \yii\db\ActiveRecord {

    public $relatedAudits = [
        'holders',
        'insureds',
        'benes',
        'planChanges',
        'writings',
        'servicings',
        'signatures',
    ];

    const DRAFT = 9; // submitted by Frontend or backend junior ops
    const PENDING = 7; // checked by ops, waiting for approval
    const DECLINED = 5; // rejected by authorised persion
    const APPROVED = 1; // approved by authorised persion
    const DELETED = 0; // soft deleted in DB level
    const SCENARIO_DRAFT = 9; // submitted by Frontend or backend junior ops
    const SCENARIO_PENDING = 7; // checked by ops, waiting for approval
    const SCENARIO_DECLINED = 5; // rejected by authorised persion
    const SCENARIO_APPROVED = 1; // approved by authorised persion
    const SCENARIO_DELETED = 0; // soft deleted in DB level

    /*
     * For activeModel dynamic property only
     */

    public $always_recalc = 1;
//    public $idplan_cat;
    public $pu;
    public $fullname;
    public $override;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'plan';
    }

    public static function getDb() {
        return Yii::$app->get('dbMaster');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['idprovider', 'idproduct', 'acc_date_locked', 'plan_status', 'd_idplan_status', 'band',
            'payment_fqy', 'premium_currency', 'plan_currency', 'si_currency', 'whole_life_payment',
            'payment_term_year', 'payment_term_month', 'whole_life', 'policy_term_year', 'policy_term_month',
            'payment_method', 'cc_expiry_month', 'cc_expiry_year', 'module',
            'portfolio_mgt', 'pm_status', 'pm_pay_method', 'pm_renew_type', 'created_by',
            'updated_by', 'deleted_by', 'status', 'd_fund_report', 'transfer_in', 'idpu_formula',
            'company', 'cc_type', 'plan_pu_recalc', 'plan_pu_redistribute', 'sell_option', 'tax_services',
            'first_premium_date_met', 'approved', 'approval_by', 'terms_paid'], 'integer'],
            [['app_rece_date', 'app_to_provider_date', 'policy_rece_date', 'policy_sent_date',
            'client_policy_receipt_date', 'client_receipt_return_date', 'acc_date', 'commencement_date',
            'd_commencement_date', 'status_change_date', 'transfer_in_date', 'transfer_out_date',
            'lapse_date', 'cancel_date', 'icp_finish_date', 'maturity_date', 'd_last_premium_date',
            'd_paid_to_date', 'd_as_of_date', 'as_of_date', 'pm_join_date', 'pm_termination_date',
            'pm_renewal_date', 'deleted_at', 'updated_at', 'df_updated_at', 'created_at',
            'protection_until', 'd_maturity_date', 'welcome_email_sentdate', 'sell_option_start_date',
            'sell_option_end_date', 'first_premium_date', 'approval_date', 'issued_date', 'next_premium_date'], 'safe'],
            [['sum_insured', 'd_sum_insured', 'initial_bonus_amount', 'initial_bonus_percentage',
            'regular_premium', 'd_regular_premium', 'net_regular_premium', 'd_net_regular_premium',
            'investment_amount', 'd_investment_amount', 'net_investment_amount', 'd_net_investment_amount',
            'loading_amount', 'd_loading_amount', 'excess_premium', 'planned_periodic_premium',
            'target_premium', 'required_annual_premium', 'upfront_charge_amount', 'advisory_fee_percentage',
            'agent_advisory_fee_percentage', 'd_annualized_premium_usd', 'annualized_premium_usd',
            'net_annualized_premium_usd', 'production_unit', 'net_production_unit', 'deductible_amount',
            'death_benefit_amount', 'd_total_premium_paid', 'accumulation_value', 'd_accumulation_value',
            'total_withdrawal', 'd_total_withdrawal', 'surrender_value', 'd_surrender_value',
            'd_future_premium_deposit', 'setup_fee', 'upfront_charge', 'factor', 'd_cash_value',
            'units', 'unit_price', 'initial_period'], 'number'],
            [['pm_renewal_remark', 'remark', 'approval_comment'], 'string'],
            [['d_product_name', 'd_plan_status', 'd_payment_fqy', 'd_plan_currency', 'd_si_currency',
            'd_payment_term_year', 'd_policy_term_year', 'd_payment_method', 'acc_name',
            'd_cc_expiry_date', 'cheque_payable_to'], 'string', 'max' => 80],
            [['caseid'], 'string', 'max' => 15],
            [['policy_no', 'application_no', 'd_plan_no', 'bank_name'], 'string', 'max' => 45],
            [['swift_code'], 'string', 'max' => 20],
            [['d_acc_cc_no', 'acc_cc_no'], 'string', 'max' => 30],
            [['d_next_premium_date', 'idplan_old', 'contingency_holder_iden', 'contingency_holder_relationship', 'rm_code'], 'string', 'max' => 40],
            [['contingency_holder', 'contingency_holder_locale', 'rm_name'], 'string', 'max' => 100],
            [['company'], 'exist', 'skipOnError' => false, 'targetClass' => Company::className(), 'targetAttribute' => ['company' => 'id']],
            //[['plan_currency', 'premium_currency', 'plan_status', 'payment_fqy', 'app_rece_date', 'payment_method'], 'required'],
            [['premium_currency'], 'exist', 'skipOnError' => false, 'targetClass' => Currency::className(), 'targetAttribute' => ['premium_currency' => 'id']],
            [['plan_currency'], 'exist', 'skipOnError' => false, 'targetClass' => Currency::className(), 'targetAttribute' => ['plan_currency' => 'id']],
            [['payment_fqy'], 'exist', 'skipOnError' => false, 'targetClass' => PaymentFrequency::className(), 'targetAttribute' => ['payment_fqy' => 'id']],
            [['plan_status'], 'exist', 'skipOnError' => false, 'targetClass' => StatusDetail::className(), 'targetAttribute' => ['plan_status' => 'id']],
            [['acc_date_locked', 'd_fund_report', 'transfer_in'], 'default', 'value' => 0],
            [['portfolio_type',
            'approval_comment', 'approval_date', 'approval_by', 'first_premium_date', 'idplan_old',
            'remark', 'df_updated_at', 'sell_option_end_date', 'sell_option_start_date', 'welcome_email_sentdate',
            'd_cash_value', 'd_maturity_date', 'protection_until', 'idpu_formula', 'factor', 'upfront_charge',
            'pm_renewal_remark', 'pm_renew_type', 'setup_fee', 'pm_pay_method', 'pm_status', 'pm_renewal_date',
            'pm_termination_date', 'pm_join_date', 'portfolio_mgt', 'as_of_date', 'd_as_of_date',
            'd_future_premium_deposit', 'd_surrender_value', 'surrender_value', 'd_total_withdrawal',
            'total_withdrawal', 'd_accumulation_value', 'accumulation_value', 'd_total_premium_paid',
            'death_benefit_amount', 'deductible_amount', 'module', 'cheque_payable_to', 'net_production_unit',
            'production_unit', 'net_annualized_premium_usd', 'annualized_premium_usd', 'd_annualized_premium_usd',
            'agent_advisory_fee_percentage', 'advisory_fee_percentage', 'upfront_charge_amount', 'd_paid_to_date',
            'd_last_premium_date', 'd_next_premium_date', 'required_annual_premium', 'planned_periodic_premium',
            'd_loading_amount', 'loading_amount', 'net_excess_premium', 'excess_premium', 'net_target_premium',
            'target_premium', 'd_net_investment_amount', 'net_investment_amount', 'd_investment_amount', 'investment_amount',
            'd_net_regular_premium', 'net_regular_premium', 'd_regular_premium', 'regular_premium',
            'cc_expiry_year', 'cc_expiry_month', 'd_cc_expiry_date', 'acc_cc_no', 'd_acc_cc_no', 'acc_name',
            'swift_code', 'bank_name', 'maturity_date', 'initial_bonus_percentage', 'initial_bonus_amount',
            'icp_finish_date', 'initial_period', 'd_payment_method', 'payment_method', 'policy_term_month',
            'policy_term_year', 'd_policy_term_year', 'whole_life', 'payment_term_month', 'payment_term_year',
            'd_payment_term_year', 'd_si_currency', 'd_sum_insured', 'sum_insured', 'si_currency', 'plan_currency',
            'd_plan_currency', 'premium_currency', 'd_payment_fqy', 'payment_fqy', 'band', 'd_plan_status',
            'plan_status', 'cancel_date', 'lapse_date', 'transfer_out_date', 'transfer_in_date', 'status_change_date',
            'd_commencement_date', 'commencement_date', 'acc_date', 'client_receipt_return_date', 'client_policy_receipt_date',
            'policy_sent_date', 'policy_rece_date', 'app_to_provider_date', 'app_rece_date', 'd_plan_no', 'application_no',
            'policy_no', 'caseid', 'd_product_name', 'issued_date', 'next_premium_date'
                ], 'default', 'value' => NULL],
            [['status'], 'default', 'value' => self::DRAFT],
            [['first_premium_date_met', 'transfer_in', 'd_fund_report', 'company', 'sell_option',
            'plan_pu_redistribute', 'plan_pu_recalc', 'cc_type', 'tax_services', 'whole_life_payment',
            'd_idplan_status', 'acc_date_locked', 'units', 'unit_price'
                ], 'default', 'value' => 0],
//            [['idprovider'], 'exist', 'skipOnError' => false, 'targetClass' => Provider::className(), 'targetAttribute' => ['idprovider' => 'id']],
//            [['idproduct'], 'exist', 'skipOnError' => false, 'targetClass' => Product::className(), 'targetAttribute' => ['idproduct' => 'id']],
            [['idprovider', 'idproduct', 'app_rece_date', 'payment_fqy', 'plan_status', 'plan_currency', 'company'],
                'required', 'when' => function ($model) {
                    return $model->status <> Plan::DRAFT;
                }, 'enableClientValidation' => false],
            [['payment_term_year'], 'required', 'when' => function ($model) {
                    return ($model->status <> Plan::DRAFT) && ($model->payment_fqy <> PaymentFrequency::FQY_LUMPSUM);
                }, 'enableClientValidation' => false],
            [['regular_premium'], 'required', 'when' => function ($model) {
                    return $model->status <> Plan::DRAFT && in_array($model->idplan_cat, [3, 5, 8, 10, 11, 17]);
                }, 'enableClientValidation' => false],
            [['net_annualized_premium_usd', 'annualized_premium_usd', 'net_regular_premium', 'regular_premium', 'investment_amount',
            'target_premium', 'excess_premium'
                ], 'double'],
            [['icp_finish_date', 'initial_period', 'commencement_date'], 'required', 'when' => function ($model) {
                    return ($model->idplan_cat == 17 && $model->status <> self::DRAFT && in_array($model->plan_status, [181])); // ILAS
                }, 'whenClient' => "function (attribute, value) {
                        return ($('#planform-plan_status').val() in ['181'] === true);
                    }"
            ],
        ];
    }

    public function setDefaultValues() {
        $this->company = 1;
        $this->premium_currency = Settings::$key['base_currency'];
        $this->app_rece_date = date(\common\helpers\IfapHelper::DATEFORMAT);
        $this->plan_currency = Settings::$key['base_currency'];
        $this->policy_term_month = 0;
        $this->payment_term_month = 0;
        $this->status = Plan::DRAFT;
    }

    public static function getPlanById($idplan) {
        $query = self::find()
                ->joinWith('holders')
                ->joinWith('insureds')
                ->joinWith('writings')
                ->joinWith('servicings')
                ->joinWith('signatures')
                ->joinWith('holders.holderPlanIdentification')
                ->joinWith('holders.holderPlanPhone')
                ->joinWith('holders.holderPlanAddress')
                ->joinWith('insureds.insuredPlanIdentification')
                ->joinWith('insureds.insuredPlanPhone')
                ->joinWith('insureds.insuredPlanAddress')
                ->joinWith('planBenes')
                ->where([
            'plan.id' => $idplan,
            'plan.status' => 1
        ]);

        $return = $query->one();
        return $return;
    }

    public static function getMonthTotalApe($month = null, $year = null) {
        if (!$month || !$year) {
            $month = date("m");
            $year = date("Y");
        }

        $query = self::find()
                ->joinWith('planStatus')
                ->joinWith('planStatus.statusMaster')
                ->where(['status_master.id' => 2])
                ->andWhere(['month(acc_date)' => $month, 'year(acc_date)' => $year, 'plan.status' => 1])
                ->sum('annualized_premium_usd');

        return $query;
    }

    public static function getYearTotalApe($year = null) {
        if (!$year) {
            $year = date("Y");
        }

        $query = self::find()
                ->joinWith('planStatus')
                ->joinWith('planStatus.statusMaster')
                ->where(['status_master.id' => 2])
                ->andWhere(['year(acc_date)' => $year, 'plan.status' => 1])
                ->sum('annualized_premium_usd');

        return $query;
    }

    public static function getMonthTotalCaseCount($month = null, $year = null) {
        if (!$month || !$year) {
            $month = date("m");
            $year = date("Y");
        }

        $query = self::find()
                ->joinWith('planStatus')
                ->joinWith('planStatus.statusMaster')
                ->where(['status_master.id' => 2])
                ->andWhere(['month(acc_date)' => $month, 'year(acc_date)' => $year, 'plan.status' => 1])
                ->count('plan.id');

        return $query;
    }

    public static function getYearTotalCaseCount($year = null) {
        if (!$year) {
            $year = date("Y");
        }

        $query = self::find()
                ->joinWith('planStatus')
                ->joinWith('planStatus.statusMaster')
                ->where(['status_master.id' => 2])
                ->andWhere(['year(acc_date)' => $year, 'plan.status' => 1])
                ->count('plan.id');

        return $query;
    }

    public function calcFirstPremiumDateDeadline() {
        $product = Product::find()->where(['id' => $this->idproduct])->one();

        if (!$product)
            return null;

        if (!empty($this->app_to_provider_date)) {
            $orb = \DateTime::createFromFormat(\common\helpers\IfapHelper::DATEFORMAT, $this->app_to_provider_date);
            $deadline = date_modify($orb, "+$product->override_bonus_days days");
            return $deadline->format(\common\helpers\IfapHelper::DATEFORMAT);
        }
        return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommApLedger() {
        return $this->hasMany(CommApLedger::className(), ['idplan' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommArLedger() {
        return $this->hasMany(CommArLedger::className(), ['idplan' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommPurchaseLedger() {
        return $this->hasMany(CommPurchaseLedger::className(), ['idplan' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommSalesLedger() {
        return $this->hasMany(CommSalesLedger::className(), ['idplan' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommTx() {
        return $this->hasMany(CommTx::className(), ['idplan' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider() {
        return $this->hasOne(Provider::className(), ['id' => 'idprovider']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyR() {
        return $this->hasOne(Company::className(), ['id' => 'company']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPremiumCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'premium_currency'])
                        ->from(Currency::tableName() . ' premiumCurrency');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'plan_currency'])
                        ->from(Currency::tableName() . ' planCurrency');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSiCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'si_currency'])
                        ->from(Currency::tableName() . ' siCurrency');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentFrequency() {
        return $this->hasOne(PaymentFrequency::className(), ['id' => 'payment_fqy']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod() {
        return $this->hasOne(PaymentMethodType::className(), ['id' => 'payment_method']);
    }

    public function getPmPaymentMethod() {
        return $this->hasOne(PaymentMethodType::className(), ['id' => 'pm_pay_method']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct() {
        return $this->hasOne(Product::className(), ['id' => 'idproduct'])
                        ->onCondition([Product::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductCategory() {
        return $this->hasOne(ProductCategory::className(), ['id' => 'idproduct_cat'])
                        ->onCondition([ProductCategory::tableName() . '.status' => 1])
                        ->viaTable(Product::tableName(), ['id' => 'idproduct']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatusDetail() {
        return $this->hasOne(StatusDetail::className(), ['id' => 'plan_status']);
    }

    public function getStatusRecord() {
        return $this->hasOne(StatusRecord::className(), ['id' => 'status']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanStatus() {
        return $this->hasOne(StatusDetail::className(), ['id' => 'plan_status']);
    }

    public function getPmStatus() {
        return $this->hasOne(StatusDetail::className(), ['id' => 'pm_status']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBenes() {
        return $this->hasMany(PlanBene::className(), ['idplan' => 'id'])
                        ->onCondition([PlanBene::tableName() . '.status' => 1, 'plan_bene.status' => 1]);
    }

    public function getPlanPeoples() {
        return $this->hasMany(PlanPeople::className(), ['idplan' => 'id'])
                        ->onCondition([PlanPeople::tableName() . '.status' => 1]);
    }

    public function getHolders() {
        return $this->hasMany(Holder::className(), ['idplan' => 'id'])
                        ->from(Holder::tableName() . ' holderpp')
                        ->andOnCondition(['holderpp.role_type' => PlanPeople::HOLDER, 'holderpp.status' => 1]);
    }

    public function getInsureds() {
        return $this->hasMany(Insured::className(), ['idplan' => 'id'])
                        ->from(Insured::tableName() . ' insuredpp')
                        ->andOnCondition(['insuredpp.role_type' => PlanPeople::INSURED, 'insuredpp.status' => 1]);
    }

    public function getWritings() {
        return $this->hasMany(Writing::className(), ['idplan' => 'id'])
                        ->from(PlanPeople::tableName() . ' writing')
                        ->onCondition([
                            'writing.role_type' => PlanPeople::WRITING,
                            'writing.status' => 1
        ]);
    }

    public function getServicings() {
        return $this->hasMany(Servicing::className(), ['idplan' => 'id'])
                        ->from(PlanPeople::tableName() . ' servicing')
                        ->onCondition([
                            'servicing.role_type' => PlanPeople::SERVICING,
                            'servicing.status' => 1
        ]);
    }

    public function getSignatures() {
        return $this->hasMany(Signature::className(), ['idplan' => 'id'])
                        ->from(PlanPeople::tableName() . ' signature')
                        ->onCondition([
                            'signature.role_type' => PlanPeople::SIGNATURE,
                            'signature.status' => 1
        ]);
    }

    public function getPeople() {
        return $this->hasMany(People::className(), ['id' => 'idpeople'])
                        ->viaTable(PlanPeople::tableName(), ['idplan' => 'id'])
                        ->onCondition([
                            'plan_people.status' => 1]);
    }

    public function getCoolingOff() {
        return $this->hasOne(CoolingOff::className(), ['id' => 'idproduct_cat'])
                        ->viaTable(ProductCategory::tableName(), ['idproduct' => 'id'])
                        ->viaTable(Product::tableName(), ['id' => 'idproduct_cat']);
    }

    public function getCrmRecordRelations() {
        return $this->hasMany(CrmRecordRelation::className(), ['idplan' => 'id'])
//                        ->from(CrmRecordRelation::tableName() . ' crmRecord')
                        ->onCondition([CrmRecordRelation::tableName() . '.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanChanges() {
        return $this->hasMany(PlanChange::className(), ['idplan' => 'id'])
                        ->from(PlanChange::tableName() . ' pc')
                        ->onCondition(['pc.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanPus() {
        return $this->hasMany(PlanPu::className(), ['idplan' => 'id'])
                        ->from(PlanPu::tableName() . ' pu')
                        ->onCondition(['pu.status' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOverrideMaster() {
        return $this->hasOne(OverrideMaster::className(), ['idproduct' => 'idproduct']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments() {
        return $this->hasMany(Document::className(), ['idplan' => 'id'])
                        ->from(Document::tableName() . ' d')
                        ->onCondition(['d.status' => 1]);
    }

    public function greatestAmount() {
        return max($this->regular_premium, $this->investment_amount, $this->target_premium);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPmType() {
        return $this->hasOne(PmType::className(), ['id' => 'pm_renew_type']);
    }

    public static function getPlanCat($idplan) {
        return self::find()
                        ->select(['c.id'])
                        ->from(self::tableName() . ' p')
                        ->innerJoin(Product::tableName() . ' d', 'p.idproduct=d.id')
                        ->innerJoin(ProductCategory::tableName() . ' c', 'd.idproduct_cat=c.id')
                        ->where([
                            'p.id' => $idplan,
                            'd.status' => 1,
                            'c.status' => 1,
                            'p.status' => 1
                        ])
                        ->scalar();
    }

    /**
     * 
     * @param type $amount
     * @param type $from idcurrency
     * @param type $frequency
     * @param type $to idcurrency
     * @return type
     */
    public static function annualizeUSD($amount, $fromCurrency, $frequency, $toCurrency = null) {
//        Yii::trace("Converting APUSD from $fromCurrency $amount to $toCurrency");
        $annualizedAmount = -1;

        if ($toCurrency == null) {
            $toCurrency = Currency::USD(); // System base currency, NOT always USD
        }

        $return_amount = Currency::exchangeCurrency($amount, $fromCurrency, $toCurrency);


        /* Annualize */
        $paymentFrequency = PaymentFrequency::findActiveOne($frequency);

        if ($paymentFrequency != NULL) {
            $annualize_factor = $paymentFrequency->annualize_factor;

            if ($annualize_factor > 0) {
                $annualizedAmount = $return_amount * $annualize_factor;
            }
        } else {
            $annualizedAmount = -1;
        }
//        Yii::trace("annualizeUSD converted to $annualizedAmount");
        return $annualizedAmount;
    }

    /**
     * THIS SHOULD ONLY REDISTRIBUTE BASIC PLAN RELATED PLAN_PU ROWS
     * EXCLUDING PLANCHANGE PLAN_PU ROWS!
     * @return bool|int
     */
    public function redistributePlanPU() {
        /*
         * Even transfer_in=1, still write a PlanPU row with pu=0
         */

        /* Update PlanPU Table if the plan master status is 1 OR 2 (NB OR Effective) */
        $masterStatus = StatusMaster::getMasterStatusByIdplan($this->id);

        if ($masterStatus == 1 || $masterStatus == 2) {
            /*
             * Delete all existing PlanPU rows, really?!
             */
            if (!PlanPu::deleteAllByIdplan($this->id)) // EXCLUDED PLAN CHANGE PU!
                return FALSE;

            $writing_agents = Writing::getAgentsByPlan($this->id);

            if (!empty($writing_agents)) {
                foreach ($writing_agents as $agent) {
                    /*
                     * 2016-6-30
                     * ASSUME PLAN C/U UI ALREADY CONTROLLED EFECTIVE WRITINGS ALWAYS = 100%
                     */

                    /*
                     * CHECK IF CURRENT WRITING EFFECTIVE DATE SATISFIES APP_RECE_DATE
                     */
                    $dtEffectiveDate = \DateTime::createFromFormat('Y-m-d', $agent->effective_date);
                    $dtEndDate = \DateTime::createFromFormat('Y-m-d', $agent->end_date);
                    $dtAppReceDate = \DateTime::createFromFormat('Y-m-d', $this->app_rece_date);

                    if ($dtEffectiveDate <= $dtAppReceDate && $dtEndDate >= $dtAppReceDate) {

                        /* Assume equal split for each writing
                          // $agent->pu_percentage = 100 / $writing_agents_count;
                          // insert a plan_pu row for each writing agent
                          // Assign PU portion to corresponding writing agents */
                        $current_agent_pu = 0;

                        if ($this->transfer_in == 1) {
                            $current_agent_pu = 0;
                        } else {
                            if ($this->production_unit > 0) {
                                if ($agent->pu_percentage > 0) {
                                    $current_agent_pu = round((float) $this->production_unit * (float) $agent->pu_percentage / 100, 2);
                                }
                            }
                        }
                        $planPU = new PlanPU();

                        $planPU->idplan = $this->id;
                        $planPU->idpeople = $agent->idpeople;
                        $planPU->pu_date = date("Y-m-d");
                        $planPU->pu = $current_agent_pu;

                        if (!$planPU->save()) {
                            return FALSE;
                        }
                    }
                }
                return true; // All writings processed
            } else
                return FALSE;
        } else {
            /* Delete all exising plan_pu records BUT DO NOT reset plan->production_unit to 0
              Plan->Production_unit should always exist for written type PU */
            return PlanPu::deleteAllByIdplan($this->id, FALSE);
        }
    }

    /**
     * This is for plan Annualized Premium USD only
     * @return bool|int
     */
    public function calculateAPU() {
        /* find out which column to use first */
        $pu_column_name = $this->productCategory->plan_pu_column_name;

        if ($pu_column_name != NULL) {
            if ($pu_column_name != "target_premium+excess_premium") {
                if (!isset($this->$pu_column_name)) {
                    return FALSE;
                }
                $amount = $this->$pu_column_name;
            } else {
                $amount = $this->target_premium + $this->excess_premium; // always same currency?
            }
            $return_amount = $this->annualizeUSD($amount, $this->premium_currency, $this->payment_fqy, Currency::USD());
        } else {
            $return_amount = 0;
        }
        return $return_amount;
    }

    /**
     * Recalc BasePlan APU, PU, PU_formula
     * @return int
     */
    public function recalcPU() {

        $this->annualized_premium_usd = $this->calculateAPU();

        if (!$this->annualized_premium_usd) {
            return FALSE;
        }
        // Calculate PU
//        $pu = new CalculatePU();
//        // Set necessary parameters to PUFormula class
//        $pu->input_policy_term_year = $this->policy_term_year;
//        $pu->input_payment_term_year = $this->payment_term_year; // ignore payment_term_month 2016-2-22 H
//        $pu->input_app_rece_date = $this->app_rece_date;
//        $pu->input_acc_date = $this->acc_date;
//        $pu->idproduct = $this->idproduct;
//        $pu->idproduct_cat = $this->product->idproduct_cat;
//        $pu->idprovider = $this->idprovider;
//        $pu->input_whole_life = $this->whole_life;
//        $pu->input_whole_life_payment = $this->whole_life_payment;
//        if ($this->regular_premium > 0) {
//            $pu->input_regular_premium = $this->annualizeUSD($this->regular_premium, $this->plan_currency, $this->payment_fqy, 2); // to USD
//        }
//        if ($this->investment_amount > 0) {
//            $pu->input_investment_amount = $this->annualizeUSD($this->investment_amount, $this->plan_currency, $this->payment_fqy, 2); // to USD
//        }
//        if ($this->loading_amount > 0) {
//            $pu->input_loading_amount = $this->annualizeUSD($this->loading_amount, $this->plan_currency, $this->payment_fqy, 2); // to USD
//        }
//        if ($this->target_premium > 0) {
//            $pu->input_target_premium = $this->annualizeUSD($this->target_premium, $this->plan_currency, $this->payment_fqy, 2); // to USD
//        }
//        if ($this->excess_premium > 0) {
//            $pu->input_excess_premium = $this->annualizeUSD($this->excess_premium, $this->plan_currency, $this->payment_fqy, 2); // to USD
//        }
//        $pu->getFactorizedPU();
//        if ($pu->final_pu < 0)
//            return FALSE;
//        // assign newly calculated PU to class
//        $this->production_unit = $pu->final_pu;
//        $this->idpu_formula = $pu->id;
//        $this->factor = $pu->totalFactor;
//
//        /* if transfer in=1, set all EXCEPT APU to zero */
//        if ($this->transfer_in == 1) {
//            $this->production_unit = 0;
//            $this->net_production_unit = 0;
//            $this->idpu_formula = 0;
//            $this->factor = 0;
//        }

        return $this->save();
    }

    /**
     * 1. Calculate net annualized premium usd for BasePlan + PlanChanges, save to Plan table immediately
     * 2. Update basePlan net_regular_premium
     * @return type
     */
    public function recalcNetAPUPU() {
        $planChanges = $this->planChanges;

        $netAPU = 0;
        $netPremium = 0;
        $netInvestment = 0;
        $netWithdrawal = 0;
        $netTargetPremium = 0;
        $netExcessPremium = 0;

        if (!empty($planChanges)) {
            foreach ($planChanges as $planChange) {
                /* Lookup plan_change_type table "PU" & "AP" columns to determine calculate APU/PU or not */
                /*
                 * PlanChange amounts already include -VE SIGN, so always ADD it up here!
                 */
                if ($planChange->planChangeType->calcAP == 1) {
                    $netAPU += $planChange->annualized_premium_usd;
                }
                /*
                 * Other columns need to include anyway
                 */
                $netPremium += $planChange->regular_premium;
                $netInvestment += $planChange->investment_amount;
                $netWithdrawal += $planChange->withdrawal_amount;
                $netTargetPremium += $planChange->target_premium;
                $netExcessPremium += $planChange->excess_premium;
            }
        }
        $this->net_regular_premium = $this->regular_premium + $netPremium;
        $this->net_investment_amount = $this->investment_amount + $netInvestment;
        $this->total_withdrawal = $netWithdrawal;
        $this->net_target_premium = $this->target_premium + $netTargetPremium;
        $this->net_excess_premium = $this->excess_premium + $netExcessPremium;
        $this->net_annualized_premium_usd = $this->annualized_premium_usd + $netAPU;

        return $this->save();
    }

    /**
     * Delete List:
     * PlanPeople
     * PlanBene
     * PlanChange
     * CrmRecords -> CrmActivity
     * Plan
     * @return bool
     */
    public function delete($hard = false) {

        // HARD DELETE tables
        PlanPeople::deleteAll(['idplan' => $this->id]);

        $documents = $this->documents;
        if ($documents) {
            foreach ($documents as $document) {
                $document->delete();
            }
        }

        // SELECTABLE tables
        if (!$hard) {
            PlanBene::softDeleteAll(['idplan' => $this->id]);
            PlanChange::softDeleteAll(['idplan' => $this->id]);
        } else {
            PlanBene::deleteAll(['idplan' => $this->id]);
            PlanChange::deleteAll(['idplan' => $this->id]);
        }

        $crmRecordRelations = $this->crmRecordRelations;

        if (!empty($crmRecordRelations)) {
            foreach ($crmRecordRelations as $crmRecordRelation) {
                if (!$hard) {
                    CrmActivity::softDeleteAll(['idcrm_record' => $crmRecordRelation->idcrm_record]);
                    CrmRecord::softDeleteAll(['id' => $crmRecordRelation->idcrm_record]);
                    $crmRecordRelation->softDelete();
                } else {
                    CrmActivity::deleteAll(['idcrm_record' => $crmRecordRelation->idcrm_record]);
                    CrmRecord::deleteAll(['id' => $crmRecordRelation->idcrm_record]);
                    $crmRecordRelation->delete();
                }
            }
        }
        if (!$hard) {
            return $this->softDelete();
        } else {
            return parent::delete();
        }
    }

    public static function findOMbyStatic($idplan) {
        $override = [
            'om' => null,
            'omc' => null,
            'omi' => null,
        ];

        $omResults = IncomeForm::getOverrideMaster($idplan);

        if ((array) ($omResults['om'])) {
            $override['om'] = $omResults['om'];
            $override['omc'] = $omResults['omc'];
            $override['omi'] = $omResults['omi'];
        }
        return $override;
    }

    public function findOM() {

        $this->override['om'] = null;
        $this->override['omc'] = null;
        $this->override['omi'] = null;
        $this->override['oa'] = null;

        $omResults = IncomeForm::getOverrideMaster($this->id);

        if ((array) ($omResults['om'])) {
            $this->override['om'] = $omResults['om'];
            $this->override['omc'] = $omResults['omc'];
            $this->override['omi'] = $omResults['omi'];

            // find out which level the writing is
//            if (!$this->writings) {
//                Yii::error("plan {$this->id} has no writing?");
//                return FALSE;
//            }
//            $ara = AgencyRelationshipAdvance::find()->where(["idpeople" => $this->writings[0]->idpeople])->one();
//
//            if (!$ara) {
//                $msg = "ARA not found";
//                Yii::error($msg);
//                return FALSE;
//            }
        }

        $override = [
            'om' => $this->override['om'],
            'omc' => $this->override['omc'],
            'omi' => $this->override['omi'],
        ];
        return $override;
    }

    public function getPlanHistories() {
        return $this->hasMany(PlanHistory::className(), ['idplan' => 'id'])
                        ->onCondition([PlanHistory::tableName() . '.status' => 1, PlanHistory::tableName() . '.idplan_change' => 0]);
    }

    /**
     * 
     * @param boolean $returnText default return integer (1-7), TRUE to return WebLang
     * @return int
     */
    public function getCheckpoint($returnText = FALSE) {

        $checkpoint = 0;

        if (!$this->statusDetail) {
            return $checkpoint;
        }

        if ($this->statusDetail->statusMaster->id == StatusMaster::INACTIVE) {
            $checkpoint = 0; // inactive;
        } else {

            // here shud be ACTIVE OR NB
            if ($this->statusDetail->statusMaster->id == StatusMaster::NB) {
                if (!$this->app_to_provider_date) {
                    $checkpoint = 1; // NB, awaiting process
                } else {
                    if ($this->commencement_date) {
                        $checkpoint = 3; // policy commenced, not issued yet
                    } else {
                        $checkpoint = 2; // NB, app sent to provider, not commenced yet
                    }
                }
            } else {
                // IN-FORCE
                if ($this->commencement_date && !$this->issued_date) {
                    $checkpoint = 4; // awaiting provider process
                } else {
                    if ($this->commencement_date && $this->issued_date) {
                        if ($this->policy_rece_date) {
                            if ($this->policy_sent_date) {
                                if ($this->client_policy_receipt_date) {
                                    if ($this->client_receipt_return_date) {
                                        $checkpoint = 7; // received back client receipt 
                                    } else {
                                        $checkpoint = 6; // sent to client, waiting for receipt
                                    }
                                } else {
                                    $checkpoint = 6; // sent to client, waiting for receipt
                                }
                            } else {
                                $checkpoint = 5; // policy at IFA 
                            }
                        } else {
                            $checkpoint = 5; // policy at IFA 
                        }
                    }
                }
            }
        }

        if (!$returnText) {
            return $checkpoint;
        } else {
            return WebLang::t("checkpoint$checkpoint");
        }
    }

    public function premiumOverdued() {

        if ($this->d_next_premium_date) {
            $checkDateField = $this->d_next_premium_date;
        } else {
            if ($this->next_premium_date) {
                $checkDateField = $this->next_premium_date;
            } else {
                return FALSE;
            }
        }
        if (\common\helpers\IfapHelper::compareDates($checkDateField, date(\common\helpers\IfapHelper::DATEFORMAT))) {
            return TRUE;
        }
        return FALSE;
    }

    public static function clearAllDfValues() {
        $elements = [
            "d_acc_cc_no",
            "d_accumulation_value",
            "d_annualized_premium_usd",
            "d_as_of_date",
            "d_cash_value",
            "d_cc_expiry_date",
            "d_commencement_date",
            "d_future_premium_deposit",
            "d_investment_amount",
            "d_last_premium_date",
            "d_loading_amount",
            "d_maturity_date",
            "d_net_investment_amount",
            "d_net_regular_premium",
            "d_next_premium_date",
            "d_paid_to_date",
            "d_payment_fqy",
            "d_payment_method",
            "d_payment_term_year",
            "d_plan_currency",
            "d_plan_no",
            "d_plan_status",
            "d_policy_term_year",
            "d_product_name",
            "d_regular_premium",
            "d_si_currency",
            "d_sum_insured",
            "d_surrender_value",
            "d_total_premium_paid",
            "d_total_withdrawal"
        ];

        $data = [];
        foreach ($elements as $element) {
            $data[$element] = NULL;
        }

        self::updateAll($data);
    }

}
