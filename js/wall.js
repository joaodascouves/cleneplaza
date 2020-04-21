window.onload = wall_image_grid_update(47, 0);

var recursive_bind = function(item, formdata)
{
  if( item.hasChildNodes() )
    Array.from(item.children).forEach(function(item, index){
      recursive_bind(item, formdata);
    });
  else
    rpc_bind(item, formdata);
}

Array.from(document.getElementsByTagName('form')).forEach(function(item, index){
  item.onsubmit = function(){
    var formdata = new FormData();
    Array.from(item.children).forEach(function(item, index){
      recursive_bind(item, formdata);
    });

    return rpc_send(item.action, formdata, rpc_callback);
  }
});
