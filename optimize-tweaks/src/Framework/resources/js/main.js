/**
 * AZ Settings Framework - Main Initializer
 *
 * @description This is the single entry point for the entire JavaScript application.
 * It controls the initialization order of all modules, ensuring dependencies are met.
 *
 * @version 1.0.0
 */
jQuery(document).ready(function($) {
    console.log("AZ Settings Framework Initializing...");

    // Initialize utility module first as it provides global functions
    if (typeof AppUtils !== 'undefined') {
        AppUtils.init();
    } else {
        console.error('AppUtils module is missing!');
    }

    // Initialize UI helpers
    if (typeof UIHelpersModule !== 'undefined') {
        UIHelpersModule.init();
    } else {
        console.error('UIHelpersModule module is missing!');
    }

    // Initialize conditional logic, which sets up the form's initial state
    if (typeof ConditionalLogicModule !== 'undefined') {
        ConditionalLogicModule.init();
    } else {
        console.error('ConditionalLogicModule module is missing!');
    }

    // Finally, initialize the main admin module, which depends on the others
    if (typeof settingsPageModule !== 'undefined') {
        settingsPageModule.init();
    } else {
        console.error('settingsPageModule module is missing!');
    }

    console.log("All framework modules have been initialized.");
});
