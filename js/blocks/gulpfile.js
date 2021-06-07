/* eslint-disable no-unused-vars */
/* eslint-disable no-console */
/* eslint-disable space-before-function-paren */
/* eslint-disable array-bracket-spacing */
/* eslint-disable quotes */
/* eslint-disable space-in-parens */
// General.
const pkg = require("./package.json");
const project = pkg.name;
const title = pkg.title;

// Build.
const buildZipDestination = "./build/";
const buildFiles = [
	"./**",
	"!build",
	"!build/**",
	"!node_modules/**",
	"!*.json",
	"!*.map",
	"!*.xml",
	"!gulpfile.js",
	"!*.sublime-project",
	"!*.sublime-workspace",
	"!*.sublime-gulp.cache",
	"!*.log",
	"!*.DS_Store",
	"!*.gitignore",
	"!TODO",
	"!*.git",
	"!*.ftppass",
	"!*.DS_Store",
	"!sftp.json",
	"!yarn.lock",
	"!*.md",
	"!package.lock",
];
const cleanFiles = [
	"./build/" + project + "/",
	"./build/" + project + " 2/",
	"./build/" + project + ".zip",
];
const buildDestination = "./build/" + project + "/";
const buildDestinationFiles = "./build/" + project + "/**/*";

// Styles.
const styleDestination = "./dist/css/";

// Scripts.
const scriptDestination = "./dist/js/";

const srcDirectory = "./build/" + project + "/src/";
const cleanSrcFiles = [
	"./build/" + project + "/src/**/*.js",
	"./build/" + project + "/src/**/*.scss",
	"!build/" + project + "/src/blocks/**/*.php",
];

/**
 * Load Plugins.
 */
const gulp = require("gulp");
const sass = require("gulp-sass");
const autoprefixer = require("gulp-autoprefixer");
const del = require("del");
const rename = require("gulp-rename");
const notify = require("gulp-notify");
const minifycss = require("gulp-uglifycss");
const replace = require("gulp-replace-task");
const zip = require("gulp-zip");
const copy = require("gulp-copy");
const cache = require("gulp-cache");
const run = require("gulp-run-command").default;
const uglify = require("gulp-uglify");
const deleteEmpty = require("delete-empty");

/**
 * Tasks.
 */
gulp.task("clearCache", function (done) {
	cache.clearAll();
	done();
});

gulp.task("clean", function (done) {
	return del(cleanFiles);
});

gulp.task("cleanSrc", function (done) {
	return del(cleanSrcFiles);
});

gulp.task("deleteEmptyDirectories", function (done) {
	deleteEmpty.sync(srcDirectory);
	console.log(deleteEmpty.sync(srcDirectory));
	done();
});

gulp.task("npmStart", run("npm run start"));

gulp.task("npmBuild", run("npm run build"));

gulp.task("npmInstall", run("npm install"));

gulp.task("copy", function () {
	return gulp.src(buildFiles).pipe(copy(buildDestination));
});

gulp.task("variables", function () {
	return gulp
		.src(buildDestinationFiles)
		.pipe(
			replace({
				patterns: [
					{
						match: "pkg.name",
						replacement: project,
					},
					{
						match: "pkg.title",
						replacement: pkg.title,
					},
					{
						match: "pkg.version",
						replacement: pkg.version,
					},
					{
						match: "pkg.author_uri",
						replacement: pkg.author_uri,
					},
					{
						match: "pkg.author",
						replacement: pkg.author,
					},
					{
						match: "pkg.license",
						replacement: pkg.license,
					},
					{
						match: "textdomain",
						replacement: pkg.name,
					},
					{
						match: "pkg.description",
						replacement: pkg.description,
					},
					{
						match: "pkg.tested_up_to",
						replacement: pkg.tested_up_to,
					},
				],
			})
		)
		.pipe(gulp.dest(buildDestination));
});

gulp.task("zip", function () {
	return gulp
		.src(buildDestination + "/**", { base: "build" })
		.pipe(zip(project + ".zip"))
		.pipe(gulp.dest(buildZipDestination));
});

gulp.task("build-notice", function () {
	return gulp.src("./").pipe(
		notify({
			message: "Your build of " + title + " is complete.",
			onLast: false,
		})
	);
});

gulp.task(
	"build-process",
	gulp.series(
		"clearCache",
		"clean",
		"npmBuild",
		"copy",
		"cleanSrc",
		"deleteEmptyDirectories",
		"variables",
		"zip",
		function (done) {
			done();
		}
	)
);

gulp.task(
	"build",
	gulp.series("build-process", "build-notice", function (done) {
		done();
	})
);

gulp.task(
	"install",
	gulp.series("npmInstall", function (done) {
		done();
	})
);

gulp.task(
	"release",
	gulp.series("build-process", function (done) {
		done();
	})
);
