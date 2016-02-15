gulp = require('gulp')
coffee = require('gulp-coffee')
coffeelint = require('gulp-coffeelint')
concat = require('gulp-concat')
less = require('gulp-less')
cssmin = require('gulp-cssmin')
argv = require('yargs').argv
check = require('gulp-if')
uglify = require('gulp-uglify')
rimraf = require('gulp-rimraf')
replace = require('gulp-replace')

# Deklaracja źródeł

# CSS
cssFiles =
  select2: ['assets/bower/select2/select2.css', 'assets/bower/select2/select2-bootstrap.css']
  colorbox: ['assets/bower/jquery-colorbox/example1/colorbox.css']

# JS
jsFiles =
  select2: ['assets/bower/select2/select2.js']
  bs_tab_trans_tooltip_collapse: ['assets/bower/bootstrap/js/{tab,transition,tooltip,collapse}.js']
  datepicker: ['assets/bower/bootstrap-datepicker/js/bootstrap-datepicker.js']
  colorbox: ['assets/bower/jquery-colorbox/jquery.colorbox-min.js']
  flot: ['node_modules/flot/{jquery.flot,jquery.flot.time,jquery.flot.pie}.js']

# Coffee
coffeeFiles =
  bs_switch: ['assets/bower/bootstrap-switch/src/coffee/bootstrap-switch.coffee']

defaultTask = ['scripts', 'styles', 'fonts']

# Przetwarzanie

# CSS
cssTasks = Object.keys(cssFiles)
cssTasks.forEach (taskName) ->
  defaultTask.push(taskName + 'css')
  gulp.task taskName + 'css', ->
    gulp.src(cssFiles[taskName])
    .pipe replace(/images\/(.*?)\.(png|gif)/g, '../../images/$1.$2')
    .pipe replace(/select2(.*?)\.(png|gif)/g, '../../images/select2$1.$2')
    .pipe cssmin()
    .pipe concat(taskName + '.min.css')
    .pipe gulp.dest('assets/css/vendors')

# JS
jsTasks = Object.keys(jsFiles)
jsTasks.forEach (taskName) ->
  defaultTask.push(taskName + 'js')
  gulp.task taskName + 'js', ->
    gulp.src(jsFiles[taskName])
    .pipe uglify()
    .pipe concat(taskName + '.min.js')
    .pipe gulp.dest('assets/js/vendors')

# Coffee
coffeeTasks = Object.keys(coffeeFiles)
coffeeTasks.forEach (taskName) ->
  defaultTask.push(taskName + 'coffee')
  gulp.task taskName + 'coffee', ->
    gulp.src(coffeeFiles[taskName])
    .pipe coffee({bare: true})
    .pipe uglify()
    .pipe concat(taskName + '.min.js')
    .pipe gulp.dest('assets/js/vendors')

# other less
gulp.task 'styles', ->
  gulp.src 'assets/less/**/*.less'
    .pipe less()
    .pipe check(!argv.development, cssmin())
    .pipe gulp.dest('assets/css')

# other coffee scripts
gulp.task 'scripts', ['lint'], ->
  gulp.src [
    'assets/coffee/**/*.coffee',
  ]
    .pipe coffee({bare: true})
    .pipe check(!argv.development, uglify())
    .pipe gulp.dest('assets/js')

gulp.task 'lint', ->
  gulp.src 'assets/coffee/**/*.coffee'
    .pipe coffeelint('coffeelint.json')
    .pipe coffeelint.reporter()
    .pipe coffeelint.reporter('fail')

gulp.task 'fonts', ->
  gulp.src 'assets/bower/bootstrap/fonts/*'
    .pipe gulp.dest('assets/fonts')

gulp.task 'clean', ->
  gulp.src ['!assets/css/prettyPhoto.css', 'assets/css/*', '!assets/js/flot', '!assets/js/flot/**',
            '!assets/js/blockui.js', '!assets/js/jquery.prettyPhoto.js', 'assets/js/*',
            'assets/fonts'], {read: false}
    .pipe rimraf()

gulp.task 'watch', ['styles', 'scripts', 'fonts'], ->
  gulp.watch ['assets/coffee/**/*.coffee'], ['scripts']
  gulp.watch ['assets/less/**/*.less'], ['styles']

gulp.task 'clean-deploy', ->
  gulp.src ['dist/*'], {read: false}
  .pipe rimraf()

gulp.task 'dist', ['clean-deploy', 'default'], ->
  gulp.src ['./assets/**/*', '!assets/{bower,coffee,less}', '!assets/{bower,coffee,less}/**', './cache', './integration/**/*',
            './config/**/*', './languages/**/*', './src/**/*', './templates/**/*', './log', './vendor/**/*', './CHANGELOG.md',
            './CONTRIBUTING.md', 'LICENSE.md', 'README.md', 'jigoshop.php'], {base: './'}
    .pipe gulp.dest('dist/')

gulp.task 'default', defaultTask
