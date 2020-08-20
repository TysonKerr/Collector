<?php

/* * *
 * Eligibility Checks
 *
 * To use eligibity checks, uncomment the function below. During login and
 * at the end of certain trials, it will receive the metadata of a particular
 * user, and it should return TRUE or FALSE.
 *
 * It should return TRUE if that participant should be allowed to continue.
 * It should return FALSE if that participant should be stopped.
 *
 * To change the message they see if they have been deemed ineligible, please
 * look at ineligible.php inside the Pages/ folder
 */

function eligibility_test($data) {
    // $data will be an array of $key => $value pairs, typically
    // the demographics. For example, if they submitted "60" as their age
    // during the demographics trial, then in this function, you would find
    // that $data['Age'] == '60'.
    
    // One thing to keep in mind is that this check happens once right when
    // they log in, so they might not have provided the demographics data
    // yet. Please make sure to check that the data point you are examining
    // actually exists before making your comparisons.
    
    // (What I mean is, use `if (isset($data['Age'])) {...}` before checking
    // if their age makes them ineligible)
    
    #  Use this block to kick out people that said they were male.
    // if (isset($data['Gender']) and $data['Gender'] === 'Male') {
    //     return false;
    // }
    
    #  Use this block to kick out people that either left their age blank,
    #  or said that they were under 60 years old.
    // if (isset($data['Age'])) {
    //     if (!is_numeric($data['Age']) or $data['Age'] < 60) {
    //         return false;
    //     }
    // }
    
    #  return true to indicate that this person is eligible to continue
    return true;
}
