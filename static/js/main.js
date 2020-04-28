document.addEventListener('DOMContentLoaded', function(){
  if( window.location.href.match(/\?context=(home|mirror|post)/) )
  {
    var d = new Date();

    var wall_script = document.createElement('script');
    wall_script.type = 'text/javascript';
    wall_script.src = '/clene2/static/js/wall.js?' + d.getTime();
    document.body.appendChild(wall_script);
  }

});
