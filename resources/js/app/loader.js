window.addEventListener("load", function(){

    // Remove Loader
    var load_screen = document.getElementById("load_screen");
    document.body.removeChild(load_screen);

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
                    lightLogo: '/img/itrac-header-logo.png.svg'
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
             equationThemeObject.settings.layout.logo.lightLogo.includes('../../src/assets/img/'))) {
            equationThemeObject.settings.layout.logo.lightLogo = '/img/itrac-header-logo.png';
        }
        // Save updated paths back to localStorage
        localStorage.setItem("theme", JSON.stringify(equationThemeObject));
    }

    // Get Dark Mode Information i.e darkMode: true or false
    
    if (equationThemeObject.settings.layout.darkMode) {
        localStorage.setItem("theme", JSON.stringify(equationThemeObject));
        getequationThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getequationThemeObject)
    
        if (getParseObject.settings.layout.darkMode) {
            ifStarterKit = document.body.getAttribute('page') === 'starter-pack' ? true : false;
            document.body.classList.add('dark');
            if (ifStarterKit) {
                if (document.querySelector('.navbar-logo')) {
                    document.querySelector('.navbar-logo').setAttribute('src', '/img/logo.svg')
                }
            } else {
                if (document.querySelector('.navbar-logo')) {
                    var darkLogoPath = getParseObject.settings.layout.logo.darkLogo;
                    // Normalize old paths to new paths
                    if (darkLogoPath && (darkLogoPath.includes('../src/assets/img/') || darkLogoPath.includes('../../src/assets/img/'))) {
                        darkLogoPath = '/img/logo.svg';
                    }
                    document.querySelector('.navbar-logo').setAttribute('src', darkLogoPath)
                }
            }
        }
    } else {
        localStorage.setItem("theme", JSON.stringify(equationThemeObject));
        getequationThemeObject = localStorage.getItem("theme");
        getParseObject = JSON.parse(getequationThemeObject)

        if (!getParseObject.settings.layout.darkMode) {
            ifStarterKit = document.body.getAttribute('page') === 'starter-pack' ? true : false;
            document.body.classList.remove('dark');
            if (ifStarterKit) {
                if (document.querySelector('.navbar-logo')) {
                    document.querySelector('.navbar-logo').setAttribute('src', '/img/itrac-header-logo.png')
                }
            } else {
                if (document.querySelector('.navbar-logo')) {
                    var lightLogoPath = getParseObject.settings.layout.logo.lightLogo;
                    // Normalize old paths to new paths
                    if (lightLogoPath && (lightLogoPath.includes('../src/assets/img/') || lightLogoPath.includes('../../src/assets/img/'))) {
                        lightLogoPath = '/img/itrac-header-logo.png';
                    }
                    document.querySelector('.navbar-logo').setAttribute('src', lightLogoPath)
                }
            }
            
        }
    }

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

