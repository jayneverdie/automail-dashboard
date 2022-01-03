<?php $this->layout('layouts/dashboard', ['title' => 'Media']);?>
<section class="content">
	<div id="elfinder"></div>
</section>

<?php $this->push('scripts'); ?>
<script data-main="/assets/elfinder/js/media-upload.js" src="/assets/elfinder/js/require.min.js"></script>
<script>
  jQuery(document).ready(function ($) {
    // refresh
    setTimeout("location.reload(true);",300000);

    // code here
    var path = '<?php echo $path; ?>';
    var access = '<?php echo $access; ?>';
    if ( typeof path !== 'undefined' && path !== '') {

      define('elFinderConfig', {
        defaultOpts : {
          url : '/media-logs.php?path=' + path + '&access=' + access,
          lang : 'en',
          uiOptions : {
            toolbar : [
              ['back', 'forward'],
              // ['reload'],
              // ['home', 'up'],
              // ['mkdir', 'mkfile'],
              ['upload'],
              ['open', 'download', 'getfile'],
              // ['info'],
              // ['quicklook'],
              ['copy', 'cut', 'paste'],
              ['rm'],
              ['duplicate', 'rename', 'edit', 'resize'],
              ['extract', 'archive'],
              ['search'],
              ['view'],
              // ['help']
            ],
            tree : {
              openRootOnLoad : true,
              syncTree : true
            },
            navbar : {
              minWidth : 150,
              maxWidth : 500
            },
            cwd : {
              oldSchool : false
            },
            contextmenu : {
              files : ['open']
            }
          },
          bootCallback : function(fm, extraObj) {
            fm.bind('init', function() {
              // any your code
            });
            var title = document.title;
            fm.bind('open', function() {
              var path = '',
                cwd  = fm.cwd();
              if (cwd) {
                path = fm.path(cwd.hash) || null;
              }
              document.title = path? path + ':' + title : title;
            }).bind('destroy', function() {
              document.title = title;
            });
          }
        },
        managers : {
          'elfinder': {}
        }
      });
    } else {
      alert('Path not set!');
    }

  });
</script>
<?php $this->end(); ?>
