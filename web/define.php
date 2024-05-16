<?php

// dbのカラムを連想配列に定義
function return_db_columns(){
    $db_columns = array(
        'id' => '',
        'pass_hash' => '',
        'institution' => '',
        'login_num' => '',
        'year' => '',
        'month' => '',
        'day' => '',
        'fix' => '',
        'age' => '',
        'gender' => '',
        'lifeStyle' => '',
        'cohabitant' => '',
        'homeProblem' => '',
        'job_bedTime' => '',
        'holiday_bedTime' => '',
        'job_fallasleepTime' => '',
        'holiday_fallasleepTime' => '',
        'job_wakeupTime' => '',
        'holiday_wakeupTime' => '',
        'job_sleepQuality' => '',
        'holiday_sleepQuality' => '',
        'job_halfway' => '',
        'holiday_halfway' => '',
        'favoriteFood' => '',
        'hatedFood' => '',
        'taste' => '',
        'sake' => '',
        'sakeType' => '',
        'sakeAmount' => '',
        'job_breakfastFreq' => '',
        'holiday_breakfastFreq' => '',
        'job_breakfastMeal' => '',
        'holiday_breakfastMeal' => '',
        'job_breakfastStartTime' => '',
        'holiday_breakfastStartTime' => '',
        'job_breakfastTime' => '',
        'holiday_breakfastTime' => '',
        'job_break_maker' => '',
        'holiday_break_maker' => '',
        'job_lunchFreq' => '',
        'holiday_lunchFreq' => '',
        'job_lunchMeal' => '',
        'holiday_lunchMeal' => '',
        'job_lunchStartTime' => '',
        'holiday_lunchStartTime' => '',
        'job_lunchTime' => '',
        'holiday_lunchTime' => '',
        'job_lunch_maker' => '',
        'holiday_lunch_maker' => '',
        'job_lunch_out_Freq' => '',
        'holiday_lunch_out_Freq' => '',
        'job_dinnerFreq' => '',
        'holiday_dinnerFreq' => '',
        'job_dinnerMeal' => '',
        'holiday_dinnerMeal' => '',
        'job_dinnerStartTime' => '',
        'holiday_dinnerStartTime' => '',
        'job_dinnerTime' => '',
        'holiday_dinnerTime' => '',
        'job_dinner_maker' => '',
        'holiday_dinner_maker' => '',
        'job_dinner_out_Freq' => '',
        'holiday_dinner_out_Freq' => '',
        'snackFreq' => '',
        'snackMeal' => '',
        'snackStartTime' => '',
        'snack_compare' => '',
        'defecation_time' => '',
        'defecation_num' => '',
        'defecation_quality' => '',
        'task' => '',
        'task_job' => '',
        'task_walk' => '',
        'task_form' => '',
        'startJob' => '',
        'overtime' => '',
        'lunchbreak' => '',
        'commute' => '',
        'commuteTime' => '',
        'tabacco' => '',
        'tabaccoNum' => '',
        'tabaccoYear' => '',
        'tabaccoQuitNum' => '',
        'tabaccoQuitYear' => '',
        'job_pedometerYes' => '',
        'holiday_pedometerYes' => '',
        'job_pedometerNo' => '',
        'holiday_pedometerNo' => '',
        'walkNum' => '',
        'walkCareer' => '',
        'walkDayOfWeek' => '',
        'otherMotion' => '',
        'otherMotionFreq' => '',
        'OtherwalkCareer' => '',
        'OtherMotionDayOfWeek' => '',
        'job_otherTimeZone' => '',
        'holiday_otherTimeZone' => '',
        'hobby' => '',
        'hobbyFreq' => '',
        'hobbyTime' => '',
        'chronicCondition' => '',
        'medicalHistory' => '',
        'job_dentifrice' => '',
        'holiday_dentifrice' => '',
        'job_stress' => '',
        'holiday_stress' => '',
        'job_stressCauses' => '',
        'holiday_stressCauses' => '',
        'holiday_Num' => '',
        'holiday' => '',
        'shopping' => '',
        'strongPoint' => '',
        'shortcoming' => '',
        'weight_3year' => '',
        'weight_2year' => '',
        'weight_1year' => '',
        'weight_cause_3year' => '',
        'weight_cause_2year' => '',
        'weight_cause_1year' => '',
        'update_time' => '',
        'feel_comment' => '',
        'agreement' => ''
    );

    return $db_columns;
};

// データベースにあげる用の連想配列
function return_db_medicalcheckup(){
    $db_medicalcheckup = array(
        'login_num' => '',
        'institution' => '',
        'year' => '',
        'month' => '',
        'day' => '',
        'obesity' => '',
        'BloodPressure' => '',
        'Dyslipidemia' => '',
        'SugarMetabolism' => '',
        'consultation_day' => '',
        'pass_hash' => '',
        "high_blood" => '', # 以下，健康診断結果
        "low_blood" => '',
        "HDL" => '',
        "LDL" => '',
        "triglyceride" => '',
        "AST" => '',
        "ALT" => '',
        "GTP" => '',
        "HbA1c" => '',
        "blood_sugar"  => ''
    );

    return $db_medicalcheckup;
}

// データベースにあげる用の連想配列
function return_db_password(){
    $db_password = array(
        // 'login_num' => '', // ログイン番号
        // 'institution' => '', // 施設名
        // 'year' => '', // 生年
        // 'month' => '', // 生月
        // 'day' => '', // 生日
        'pass_hash' => '', // ハッシュ化された個人情報
        'mailadress' => '', // メールアドレス
        'password' => '' // パスワード
    );

    return $db_password;
}

?>