import fs from 'fs';
import path from 'path';
import postcss from 'postcss';

// User specified explicitly allowed properties
const allowedProperties = new Set([
    'color', 'background', 'background-color', 'border-color', 
    'outline-color', 'box-shadow', 'fill', 'stroke', 'opacity', 
    'caret-color', 'text-decoration-color'
]);

const filesToProcess = [
    "resources/css/app/dark/main.css",
    "resources/css/app/dark/structure.css",
    "resources/css/app/custom.css",
    "public/css/account-setting/page-specific/dark/settings.css",
    "public/css/account-setting/page-specific/dark/user-profile.css",
    "public/css/account-setting/page-specific/dark/account-settings.css",
    "public/css/account-setting/page-specific/dark/tabs.css",
    "public/css/account-setting/page-specific/dark/chat.css",
    "public/css/head/dashboard/page-specific/dark/dash_1.css",
    "public/css/head/dashboard/page-specific/dark/dt-global_style.css",
    "public/css/admin/dashboard/page-specific/dark/dt-global_style.css",
    "public/css/general-pages/tasks/page-specific/dark/modal.css"
];

const basePath = "c:/Capstone/CAPSTONE - ITRAC/I-TRAC";

async function processFiles() {
    let removedLog = [];

    const plugin = () => {
        return {
            postcssPlugin: 'strip-structural',
            Once(root) {
                root.walkRules(rule => {
                    // Target any rule that contains .dark or body.dark
                    if (rule.selector.includes('.dark')) {
                        rule.walkDecls(decl => {
                            const prop = decl.prop;
                            if (!allowedProperties.has(prop) && !prop.startsWith('--')) {
                                removedLog.push(`Removed [${prop}: ${decl.value}] from ${rule.selector}`);
                                decl.remove();
                            }
                        });
                    }
                });
            }
        };
    };
    plugin.postcss = true;

    for (const file of filesToProcess) {
        const fullPath = path.resolve(basePath, file);
        if (!fs.existsSync(fullPath)) {
            console.log(`Missing file: ${file}`);
            continue;
        }

        const css = fs.readFileSync(fullPath, 'utf8');
        try {
            const result = await postcss([plugin()]).process(css, { from: fullPath, to: fullPath });
            fs.writeFileSync(fullPath, result.css);
            console.log(`Processed ${file}`);
        } catch (e) {
            console.error(`Error processing ${file}:`, e);
        }
    }

    const logPath = path.resolve(basePath, "removed_properties.log");
    fs.writeFileSync(logPath, removedLog.join('\n'), 'utf8');
    console.log(`\nDone. Removed ${removedLog.length} properties.`);
    console.log(`Detailed log saved to ${logPath}`);
}

processFiles();
