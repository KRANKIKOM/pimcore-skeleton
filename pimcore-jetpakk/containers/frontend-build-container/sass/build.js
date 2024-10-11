'use strict'
const chokidar = require('chokidar');
const { debounce } = require('throttle-debounce');
const sass = require('sass')
const fs = require('fs');
const path = require('path');
const glob = require('glob');

var sass_css_basefolder = '/var/www/html/public/css/';
if (typeof(process.env.SASS_CSS_BASEFOLDER) !== 'undefined') {
    sass_css_basefolder = process.env.SASS_CSS_BASEFOLDER;
    if(sass_css_basefolder.substr(-1) !== '/') {
            sass_css_basefolder = sass_css_basefolder+"/";
    }
}


const scss_filename = sass_css_basefolder+'main.scss';
const css_filename = sass_css_basefolder+'main.css';
const map_filename = sass_css_basefolder+'main.map';


let firstBuild = true;
let buildSassDebounced = null;

let watchMode = true;
if (typeof(process.env.SASS_NOWATCH) !== 'undefined') {
    watchMode = false;
}
const sourceMapEmbed = watchMode; // in watchmode (===dev setup), we just embed the sourceMap

let sassOptArr = [];

const buildSassOpts = async function () {
    return new Promise((resolve, reject) => {
        glob(sass_css_basefolder+"*.scss", {nonull:false, absolute:true}, function (er, files) {
            console.log("Building options for " + files.length + " scss files");
            files.forEach(scss_filename => {
                console.log("Building options for '" + scss_filename+"'");
                const css_filename = scss_filename.replace(".scss",".css");
                const map_filename = scss_filename.replace(".scss",".map");
                const sassOpts = {
                    file: scss_filename,
                    outFile: css_filename,
                    sourceMap: path.basename(map_filename),
                    sourceMapFullPath: map_filename,
                    sourceMapEmbed: sourceMapEmbed
                };
                console.log("Built options for '" + scss_filename+"'", sassOpts);
                sassOptArr.push(sassOpts);
            });
            resolve();
        });
    });
};



const buildSass = async function (lastEvent) {
    console.log("*** TRIGGERING SASS ***");

    for (const sassOpts of sassOptArr) {
        console.log(sassOpts);
        await buildSingleSass(sassOpts);
    }

};

const buildSingleSass = async function (sassOpts) {
    console.log("*** BUILDING SASS! ***", sassOpts.file, sassOpts.outFile);

    return new Promise((resolve, reject) => {
        sass.render(sassOpts, function (err, result) {


            if (!err) {
                console.log("SASS BUILD DONE",sassOpts.outFile,result.stats.duration,"ms",result.css.length,"bytes");
                fs.writeFile(sassOpts.outFile, result.css, function (err) {
                    if (err) {
                        console.error("Failed to write",css_filename,err);
                    }
                });

                if (!sourceMapEmbed) {
                    fs.writeFile(sassOpts.sourceMapFullPath, result.map, function (err) {
                        if (err) {
                            console.error("Failed to write",map_filename,err);
                        }
                    });
                }

                resolve();
            } else {
                console.error("SASS BUILD FAILED", err);
                reject(err);
            }
            if (firstBuild) {
                //console.log("LOWERING INTERVAL");
                firstBuild=false;
                buildSassDebounced = debounce(10, buildSass);
            }



        });

    });

};

buildSassDebounced = debounce(5000, buildSass);

// One-liner for current directory
const chokidarOpts = {
    usePolling: true,
    awaitWriteFinish: true,
    ignored: ['fonts/**','*.eot','*.ttf','*.woff','*.woff2'],
    interval: 200
};

if (watchMode) {
    buildSassOpts();

    console.log("Using watchmode");
    chokidar.watch(sass_css_basefolder+'**/*.scss', chokidarOpts).on('all', (event, path) => {
        if (!firstBuild) {
            console.log("File modification event - ", event, "-", path);
        }
        buildSassDebounced(path);
    });

} else {
    console.log("Building without watchmode");
    buildSassOpts().then(buildSass);
}
