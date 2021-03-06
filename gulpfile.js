'use strict';

var gulp = require('gulp');
var browserSync = require('browser-sync').create();
var reload = browserSync.reload;
var watch = require('gulp-watch');
var phpunit = require('gulp-phpunit');
var sftp = require('gulp-sftp');
var chmod = require('gulp-chmod');
var FTPCONFIG = require('./ftpconfig.js');

var destpath = "/Library/WebServer/Documents/photo-conv/";

gulp.task('deploy-plugins', function() {
  return gulp.src(['node_modules/jquery/dist/jquery.min.js', 'node_modules/bootstrap/dist/**/*.*'])
    .pipe(sftp({
      host: FTPCONFIG.FTP_URL,
      auth: 'privateKey',
      remotePath: FTPCONFIG.FTP_REMOTEPATH+'/plugins'
    }));
});

gulp.task('deploy-web', function() {
  return gulp.src('web/*')
    .pipe(sftp({
      host: FTPCONFIG.FTP_URL,
      auth: 'privateKey',
      remotePath: FTPCONFIG.FTP_REMOTEPATH
    }));
});

gulp.task('deploy', ['deploy-plugins', 'deploy-web'], function() {
  return gulp.src('web/plugins/pel/**/*')
    .pipe(sftp({
      host: FTPCONFIG.FTP_URL,
      auth: 'privateKey',
      remotePath: FTPCONFIG.FTP_REMOTEPATH+"/plugins/pel"
    }));
});

gulp.task('web', function() {
  gulp.src("web/*.*")
    .pipe(gulp.dest(destpath));
  gulp.src("node_modules/jquery/dist/jquery.min.js")
    .pipe(gulp.dest(destpath+"plugins/"));
  gulp.src("node_modules/bootstrap/dist/**/*.*")
    .pipe(gulp.dest(destpath+"plugins/"));
  gulp.src("web/plugins/pel/**/*.*")
    .pipe(gulp.dest(destpath+"plugins/pel/"));
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
