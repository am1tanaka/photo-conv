'use strict';

var gulp = require('gulp');
var browserSync = require('browser-sync').create();
var reload = browserSync.reload;
var watch = require('gulp-watch');
var phpunit = require('gulp-phpunit');

var destpath = "/Library/WebServer/Documents/photo-conv/";

gulp.task('phpunit', function() {
  gulp.src('tests/phpunit.xml').pipe(phpunit('/usr/local/bin/phpunit'));
});

gulp.task('web', function() {
  gulp.src("web/*.*")
    .pipe(gulp.dest(destpath));
  gulp.src("node_modules/jquery/dist/jquery.min.js")
    .pipe(gulp.dest(destpath+"plugins/"));
  gulp.src("node_modules/bootstrap/dist/**/*.*")
    .pipe(gulp.dest(destpath+"plugins/"));
});

gulp.task('reload', function() {
  reload();
});

// 変更とライブリロードの監視を起動
gulp.task('watch', ['web'], function() {
  browserSync.init({
    proxy: 'localhost/photo-conv/'
  });

  gulp.watch([
    'web/*.html',
    'web/*.php'],
    ['web','reload']
  );
});

gulp.task('default', ['watch']);
