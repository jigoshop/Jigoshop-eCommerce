gulp = require('gulp')
coffee = require('gulp-coffee')
coffeelint = require('gulp-coffeelint')
concat = require('gulp-concat')
less = require('gulp-less')
cssmin = require('gulp-cssmin')
check = require('gulp-if')
rimraf = require('gulp-rimraf')
replace = require('gulp-replace')
wpPot = require('gulp-wp-pot')
uglifyes = require('uglify-es')
babel = require('gulp-babel')
composer = require('gulp-uglify/composer')
uglify = composer(uglifyes, console)
minify = true
# Sources declaration

# CSS
cssFiles =
  select2: ['assets/bower/select2/select2.css', 'assets/bower/select2/select2-bootstrap.css']
  tokenfield: ['assets/bower/bootstrap-tokenfield/dist/css/bootstrap-tokenfield.css']
  blueimp: ['node_modules/blueimp-gallery/css/blueimp-gallery.css']
  impromptu: ['assets/bower/jquery-impromptu/dist/themes/base.css']
  magnific_popup: ['assets/bower/magnific-popup/dist/magnific-popup.css']

# JS
jsFiles =
  select2: ['assets/bower/select2/select2.js']
  bs_tab_trans_tooltip_collapse: ['assets/bower/bootstrap/js/{tab,transition,tooltip,collapse}.js']
  datepicker: ['assets/bower/bootstrap-datepicker/js/bootstrap-datepicker.js']
  tokenfield: ['assets/bower/bootstrap-tokenfield/js/bootstrap-tokenfield.js']
  flot: ['node_modules/jquery-flot/{jquery.flot,jquery.flot.stack,jquery.flot.pie,jquery.flot.resize,jquery.flot.time}.js']
  blueimp: ['node_modules/blueimp-gallery/js/{blueimp-gallery.js,jquery.blueimp-gallery.js}']
  bs_switch: ['assets/bower/bootstrap-switch/dist/js/bootstrap-switch.js']
  impromptu: ['assets/bower/jquery-impromptu/dist/jquery-impromptu.js']
  magnific_popup: ['assets/bower/magnific-popup/dist/jquery.magnific-popup.min.js']

# Coffee
coffeeFiles = []


defaultTask = ['scripts', 'styles', 'fonts', 'refresh-pot-file']

# Processing

# CSS
cssTasks = Object.keys(cssFiles)
cssTasks.forEach (taskName) ->
  defaultTask.push(taskName + 'css')
  gulp.task taskName + 'css', ->
    gulp.src(cssFiles[taskName])
    .pipe replace(/images\/(.*?)\.(png|gif)/g, '../../images/$1.$2')
    .pipe replace(/select2(.*?)\.(png|gif)/g, '../../images/select2$1.$2')
    .pipe check(minify, cssmin())
    .pipe concat(taskName + '.css')
    .pipe gulp.dest('assets/css/vendors')

# JS
jsTasks = Object.keys(jsFiles)
jsTasks.forEach (taskName) ->
  defaultTask.push(taskName + 'js')
  gulp.task taskName + 'js', ->
    gulp.src(jsFiles[taskName])
    .pipe check(minify && taskName != 'blueimp' && taskName != 'flot', uglify())
    .pipe concat(taskName + '.js')
    .pipe gulp.dest('assets/js/vendors')

# Coffee
coffeeTasks = Object.keys(coffeeFiles)
coffeeTasks.forEach (taskName) ->
  defaultTask.push(taskName + 'coffee')
  gulp.task taskName + 'coffee', ->
    gulp.src(coffeeFiles[taskName])
    .pipe coffee({bare: true})
    .pipe uglify()
    .pipe concat(taskName + '.js')
    .pipe gulp.dest('assets/js/vendors')

# other less
gulp.task 'styles', ->
  gulp.src 'assets/less/**/*.less'
    .pipe less()
    .pipe check(minify, cssmin())
    .pipe gulp.dest('assets/css')

# other coffee scripts
gulp.task 'scripts', ['lint'], ->
  gulp.src [
    'assets/coffee/**/*.coffee',
  ]
    .pipe coffee({bare: true})
    .pipe check(minify, uglify({
      mangle: false,
      ecma: 6
    }))
    .pipe babel({presets: ['env']})
    .on('error', (err) ->
      gutil.log(gutil.colors.red('[Error]'), err.toString())
    )
    .pipe gulp.dest('assets/js')

gulp.task 'lint', ->
  gulp.src 'assets/coffee/**/*.coffee'
    .pipe coffeelint('coffeelint.json')
    .pipe coffeelint.reporter()
    .pipe coffeelint.reporter('fail')

gulp.task 'fonts', ->
  gulp.src 'assets/bower/bootstrap/fonts/*'
    .pipe gulp.dest('assets/fonts')

gulp.task 'refresh-pot-file', ->
  gulp.src  ['src/**/*.php', 'templates/**/*.php', 'jigoshop.php']
    .pipe wpPot
      domain: 'jigoshop-ecommerce'
      package: 'Jigoshop eCommerce'
    .pipe gulp.dest('languages/jigoshop-ecommerce.pot')

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
            './CONTRIBUTING.md', 'LICENSE.md', 'README.md', 'jigoshop.php', 'readme.txt'], {base: './'}
    .pipe gulp.dest('dist/')

gulp.task 'dev', ['do-not-minify', 'clean-deploy', 'default']

gulp.task 'do-not-minify', ->
  minify = false

gulp.task 'default', defaultTask
