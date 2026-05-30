window.addEventListener("load", function(){

    // Remove Loader
    var load_screen = document.getElementById("load_screen");
    if (load_screen && load_screen.parentNode) {
        document.body.removeChild(load_screen);
    }

    var layoutName = 'Horizontal Light Menu';
    var ifStarterKit; // Added declaration for ifStarterKit

    var settingsObject = {
        admin: 'Equation Admin Template',
        settings: {
            layout: {
                name: layoutName,
                toggle: true,
                darkMode: false,
                boxed: true,
                logo: {
                    darkLogo: '/img/logo.svg',
                    lightLogo: '/img/itrac-header-logo.png'
                }
            }
        },
        reset: false
    }

    if (settingsObject.reset) {
        localStorage.clear()
    }

    if (localStorage.length === 0) {
        var equationThemeObject = settingsObject; // Added 'var' declaration
    } else {

        var getequationThemeObject = localStorage.getItem("theme"); // Added 'var' declaration
        var getParseObject = JSON.parse(getequationThemeObject)
        var ParsedObject = getParseObject;

        if (getequationThemeObject !== null) {
               
            if (ParsedObject.admin === 'Equation Admin Template') {

                if (ParsedObject.settings.layout.name === layoutName) {

                    equationThemeObject = ParsedObject;
                } else {
                    equationThemeObject = settingsObject;
                }
                
            } else {
                if (ParsedObject.admin === undefined) {
                    equationThemeObject = settingsObject;
                }
            }

        }  else {
            equationThemeObject = settingsObject;
        }
    }

    // Migrate old logo paths to new paths
    if (equationThemeObject.settings && equationThemeObject.settings.layout && equationThemeObject.settings.layout.logo) {
        if (equationThemeObject.settings.layout.logo.darkLogo && 
            (equationThemeObject.settings.layout.logo.darkLogo.includes('../src/assets/img/') || 
             equationThemeObject.settings.layout.logo.darkLogo.includes('../../src/assets/img/'))) {
            equationThemeObject.settings.layout.logo.darkLogo = '/img/logo.svg';
        }
        if (equationThemeObject.settings.layout.logo.lightLogo && 
            (equationThemeObject.settings.layout.logo.lightLogo.includes('../src/assets/img/') || 
             equationThemeObject.settings.layout.logo.lightLogo.includes('../../src/assets/img/') ||
             equationThemeObject.settings.layout.logo.lightLogo.includes('.png.svg'))) {
            equationThemeObject.settings.layout.logo.lightLogo = '/img/itrac-header-logo.png';
        }
        // Save updated paths back to localStorage
        localStorage.setItem("theme", JSON.stringify(equationThemeObject));
    }

    // Dark mode state is now managed globally by ThemeManager and the <head> inline script.

    // Get Layout Information i.e boxed: true or false

    if (equationThemeObject.settings.layout.boxed) {
    
        localStorage.setItem("theme", JSON.stringify(equationThemeObject));
        getequationThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getequationThemeObject)
    
        if (getParseObject.settings.layout.boxed) {
            
            if (document.body.getAttribute('layout') !== 'full-width') {
                document.body.classList.add('layout-boxed');
                if (document.querySelector('.header-container')) {
                    // document.querySelector('.header-container').classList.add('container-xxl');
                }
                if (document.querySelector('.middle-content')) {
                    document.querySelector('.middle-content').classList.add('container-xxl');
                }
            } else {
                document.body.classList.remove('layout-boxed');
                if (document.querySelector('.header-container')) {
                    document.querySelector('.header-container').classList.remove('container-xxl');
                }
                if (document.querySelector('.middle-content')) {
                    document.querySelector('.middle-content').classList.remove('container-xxl');
                }
            }
            
        }
        
    } else {
        
        localStorage.setItem("theme", JSON.stringify(equationThemeObject));
        getequationThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getequationThemeObject)
        
        if (!getParseObject.settings.layout.boxed) {

            if (document.body.getAttribute('layout') !== 'boxed') {
                document.body.classList.remove('layout-boxed');
                if (document.querySelector('.header-container')) {
                    document.querySelector('.header-container').classList.remove('container-xxl');
                }
                if (document.querySelector('.middle-content')) {
                    document.querySelector('.middle-content').classList.remove('container-xxl');
                }
            } else {
                document.body.classList.add('layout-boxed');
                if (document.querySelector('.header-container')) {
                    // document.querySelector('.header-container').classList.add('container-xxl');
                }
                if (document.querySelector('.middle-content')) {
                    document.querySelector('.middle-content').classList.add('container-xxl');
                }
            }
        }
    }

    


    
});

