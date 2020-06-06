var old_title = document.title;
var old_count = 0;
var current_count = 0;

var comment_count = document.getElementById('comment-count');

document.addEventListener('visibilitychange', function (event) {
  if (document.visibilityState === 'visible') {
    document.title = old_title;
    old_count = current_count;
  }
});

function rpc_bind(item, formdata) {
  if (['input', 'textarea'].includes(item.tagName.toLowerCase()))
    if (item.type.toLowerCase() != 'file')
      formdata.append(item.name, item.value);
    else
      formdata.append(item.name, item.files[0]);
}

var rpc_oncourse = false;
function rpc_send(action, data, callback) {
  if (rpc_oncourse === true)
    return false;

  rpc_oncourse = true;

  var request = new XMLHttpRequest();
  request.open('POST', action, true);
  request.send(data);

  request.onload = function () {
    if (request.status == 200)
      callback(request.responseText);

    rpc_oncourse = false;
  }

  return false;
}

function modal_display(message) {
  var center_modal = document.getElementById('center-modal');
  if (center_modal) {
    var span = (document.getElementById('modal-span') ?
      document.getElementById('modal-span') : document.createElement('span'));

    span.id = 'modal-span';
    span.innerHTML = message;

    center_modal.appendChild(span);
    center_modal.style.display = 'inherit';
  }
  else
    console.log(data);
}

function rpc_callback(data) {
  var parsed_data = JSON.parse(data);
  if (parsed_data.status)
    modal_display(parsed_data.message);

  if (document.getElementById('comment_form') && parsed_data.status !== 100) {
    document.getElementById('comment_form').reset();
    document.getElementsByName('message')[0].value = '';
  }

  if (document.getElementById('send_file') && parsed_data.status !== 100)
    document.getElementById('send_file').disabled = true;

  if (document.getElementById('wall_image_preview') && parsed_data.status !== 100)
    document.getElementById('wall_image_preview').src = '/clenexyz/foto.jpg';
}

/**************/
function comments_update_callback(data) {
  var parsed_data = JSON.parse(data);
  if (!parsed_data || parsed_data.status !== 0)
    return rpc_callback(data);

  var wall = document.getElementById('comments-wall-container');
  var wall_json = parsed_data.wall;
  if (wall_json.length < 1)
    return;

  var collection = document.getElementsByClassName('comment-container');
  if (collection.length >= 48)
    wall_json.forEach(function (item, index) {
      if (collection.length > 1)
        collection[collection.length - 1].remove();
    });

  var entries = [];
  wall_json.forEach(function (item, index) {
    entries.push(wall_grid_insert(wall_json[index], 'comment-container post-container', 'post'));
  });

  entries.reverse();

  for (var i = 0; i < entries.length; i++) {
    wall.insertBefore(entries[i], wall.lastChild.nextSibling);

    if (comment_count)
      comment_count.innerHTML = parseInt(comment_count.innerHTML) + 1;
  }

  if (!document.hasFocus()) {
    current_count = parseInt(comment_count.innerHTML);

    if (old_count === 0)
      old_count = (current_count === 0 ? 2 : 1) - 1;

    document.title = '(' + parseInt(current_count - old_count) + ') ' + old_title;
  }
}
/**************/

function moderate(action, id, object, modal) {
  var formdata = new FormData();
  var target = (entry_id ? 'comment' : context);

  formdata.append('object', target + 's');
  formdata.append('action', action);
  formdata.append('entry_id', id);

  rpc_send('api.php?context=moderate&action=action', formdata, function (data) {

    var parsed_data = JSON.parse(data);
    if (modal)
      modal_display(parsed_data.message);

    if (parsed_data.status === 0) {
      switch (action) {
        case 'delete':
          // object.style.visibility = 'hidden';
          object.style.display = 'none';
          break;
      }
    }

  });
}

function wall_grid_insert(image, container, image_class) {
  var entry_container = document.createElement('div');
  entry_container.className = container;


  if (image.file_name) {
    var entry_display = document.createElement('div');
    entry_display.className = image_class + '-file';

    var entry_file = document.createElement('img');
    entry_file.className = image_class + '-file-file';
    entry_file.src = image.file_path;
    entry_display.appendChild(entry_file);
  }
  else {
    var entry_display = document.createElement('img');
    entry_display.className = image_class + '-file';
    entry_display.src = image.file_path;
  }

  var post_link = document.createElement('a');
  post_link.innerHTML = "#" + image.ID;

  if (!entry_id)
    post_link.href = '?context=' + context + '&entry_id=' + image.ID + '&action=show';

  var profile_link = document.createElement('a');
  profile_link.href = '?context=profile&profile_id=' + image.user_id;
  profile_link.innerHTML = ' ~' + image.creator;

  var entry_id = document.createElement('div');
  entry_id.classList.add(image_class + '-label', image_class + '-label-top-left');
  entry_id.appendChild(post_link);
  entry_id.appendChild(profile_link);

  var post_stats = document.createElement('div');
  post_stats.classList.add(image_class + '-label', image_class + '-label-top-right');
  post_stats.innerHTML = image.stats;

  if (image.opts) {
    post_stats.innerHTML += '(';

    var index = 0;
    var num_opts = Object.values(image.opts).length;

    for (key in image.opts) {
      var link = document.createElement('a');
      link.innerHTML = key;
      link.href = '#/';
      link.setAttribute('onclick', 'javascript:moderate("' + image.opts[key] + '",' + image.ID + ', this.parentNode.parentNode, true)');
      link.setAttribute('oncontextmenu', 'javascript:moderate("' + image.opts[key] + '",' + image.ID + ', this.parentNode.parentNode, false); return false');

      post_stats.appendChild(link);

      if (++index < num_opts)
        post_stats.innerHTML += '|';
    }

    post_stats.innerHTML += ')';
  }

  if (image.label && image.label !== '') {
    var entry_label = document.createElement('div');
    entry_label.classList.add(image_class + '-label', image_class + '-label-label');
    entry_label.innerHTML = image.label;
  }

  entry_container.appendChild(entry_id);
  entry_container.appendChild(entry_display);

  if (image.file_name) {
    var entry_file_name = document.createElement('div');
    var entry_file_link = document.createElement('a');

    entry_file_name.className = image_class + '-label-file-name';
    entry_file_link.href = image.file_path;
    entry_file_link.innerHTML = image.file_name;

    entry_file_name.appendChild(entry_file_link);
    entry_display.appendChild(entry_file_name);
  }

  if (image.label && image.label !== '')
    entry_container.appendChild(entry_label);

  entry_container.appendChild(post_stats);

  return entry_container;
}

var entry_id = window.location.href.match(/&entry_id=([0-9]+)/);

var current_collection =
  document.getElementsByClassName((!entry_id ?
    'wall-image-container' :
    'comment-container'
  ));

function wall_grid_get_count() {
  return (current_collection ?
    current_collection.length : 0);
}

function wall_grid_get_last_id() {
  var modifier = (entry_id ? 1 : 0);
  return (current_collection && current_collection.length < 2 ?
    0 : parseInt(current_collection[1 - modifier].innerHTML.split('#')[1]));
}

function wall_grid_get_first_id() {
  var modifier = (entry_id ? 1 : 0);
  var last_hidden = 1;

  if (!modifier)
    last_hidden = (current_collection[current_collection.length - 1].style.display === 'none' && !entry_id ? 2 : 1);

  return (current_collection && current_collection.length < 2 - modifier ?
    0 : parseInt(current_collection[current_collection.length - last_hidden].innerHTML.split('#')[1]));
}

function wall_grid_pagination_update() {
  var first_id = wall_grid_get_first_id();
  var last_id = wall_grid_get_last_id();

  var pagination = document.getElementById('wall-pagination-container');
  var page_match = window.location.href.match(/&page=([0-9]+)/);

  var page_num = (page_match ? parseInt(page_match[1]) : 0);

  var prev_button, next_button;
  if (wall_grid_get_count() > 48) {
    next_button = document.getElementById('next_button');
    if (!next_button) {
      next_button = document.createElement('a');
      next_button.id = 'next_button';
      next_button.innerHTML = 'Next';
    }

    next_button.href = '?context=home&offset=' + first_id + '&direction=DESC&page=' + parseInt(page_num + 1);
  }

  if (window.location.href.match(/offset=([0-9]+)&direction=(ASC|DESC)/)) {
    prev_button = document.getElementById('prev_button');
    if (!prev_button) {
      prev_button = document.createElement('a');
      prev_button.id = 'prev_button';
      prev_button.innerHTML = 'Previous';
    }

    prev_button.href = '?context=home';

    if (page_num > 1)
      prev_button.href += '&offset=' + last_id + '&direction=ASC&page=' + parseInt(page_num - 1);
  }

  if (prev_button) pagination.appendChild(prev_button);
  if (next_button) pagination.appendChild(next_button);
}

function wall_grid_update_callback(data) {
  var parsed_data = JSON.parse(data);
  if (!parsed_data || parsed_data.status !== 0)
    return rpc_callback(data);

  document.getElementById('balance').innerHTML = parsed_data.balance;

  var wall = document.getElementById('wall-container');
  if (!wall)
    return;

  var wall_json = parsed_data.wall;
  if (wall_json.length < 1)
    return;

  var collection = document.getElementsByClassName('wall-image-container');
  if (collection.length > 48)
    wall_json.forEach(function (item, index) {
      if (collection.length > 1)
        collection[collection.length - 1].remove();
    });

  var images = [];
  wall_json.forEach(function (item, index) {
    images.push(wall_grid_insert(wall_json[index], 'wall-image-container', 'wall-image'));
  });

  images.reverse();
  for (var i = 0; i < images.length; i++) {
    if (!wall.firstElementChild)
      wall.appendChild(images[i]);

    else {
      if (wall.firstElementChild.id == 'upload_box')
        wall.insertBefore(images[i], wall.firstElementChild.nextSibling);
      else
        wall.insertBefore(images[i], wall.firstChild);
    }
  }

  if (collection.length >= 48 && document.getElementById('upload_box').style.display != 'none')
    collection[collection.length - 1].style.display = 'none';

  if (!document.hasFocus()) {
    current_count += images.length;

    if (old_count === 0)
      old_count = (current_count - images.length) - (current_count === images.length ? 1 : 0);

    document.title = '(' + parseInt(current_count - old_count - 1) + ') ' + old_title;
  }

  wall_grid_pagination_update();
}

function wall_grid_update(offset, direction, limit) {
  if (!context || context.length === 0)
    return;

  var formdata = new FormData();
  formdata.append('offset', offset);
  formdata.append('direction', direction);
  formdata.append('limit', limit);

  if (entry_id) {
    formdata.append('entry_id', entry_id[1]);
    formdata.append('context', context);
  }

  if (context === 'post' && !entry_id) {
    formdata.append('saloon', saloon);
  }

  rpc_send('api.php?context=' + (entry_id ? 'comment' : context) + '&action=collection_fetch', formdata,
    (entry_id ? comments_update_callback : wall_grid_update_callback));
}

function wall_image_preview_refresh(event) {
  var preview = document.getElementById('wall_image_preview');
  if (preview) {
    preview.src = URL.createObjectURL(event.target.files[0]);
    document.getElementById('send_file').disabled = false;
  }
}

(function () {
  /*
    Navigates recursively through document forms, binding the RPC action
    in each input. Necessary to async image upload.
  */
  var recursive_bind = function (item, formdata) {
    if (item.hasChildNodes())
      Array.from(item.children).forEach(function (item, index) {
        recursive_bind(item, formdata);
      });
    else
      rpc_bind(item, formdata);
  }

  Array.from(document.getElementsByTagName('form')).forEach(function (item, index) {
    if (item.method !== 'get') {
      item.onsubmit = function () {
        var formdata = new FormData();
        Array.from(item.children).forEach(function (item, index) {
          recursive_bind(item, formdata);
        });

        return rpc_send(item.action, formdata, rpc_callback);
      }
    }
  });
  /*
    End of recursive binding.
  */
  if (document.getElementById('comments-wall-container')) {
    document.getElementById('comment_form').action = 'api.php?context=comment&action=message_insert';

    if (['post', 'mirror'].includes(context.toLowerCase())) {
      wall_grid_update(wall_grid_get_first_id(), 'ASC', 0);
      setInterval(function () {
        wall_grid_update(wall_grid_get_first_id(), 'ASC', 0);

      }, 3000);
    }
  }

  if (!document.getElementById('wall-container'))
    return;

  document.getElementById('sync_trigger').addEventListener('change', function (event) {
    if (event.target.checked) {
      if (!window.location.href.match(/&sync=true/))
        location.href = '?context=' + (context === 'post' ? 'home' : context) +
          ('saloon' in window ? '&saloon=' + saloon : '') + '&sync=true';
    }
    else
      location.href = '?context=' + (context === 'post' ? 'home' : context) + '&sync=false';
  });

  document.getElementById('post_trigger').addEventListener('change', function (event) {
    var upload_box = document.getElementById('upload_box');
    var images_collection = document.getElementsByClassName('wall-image-container');
    var last_image = (images_collection.length > 48 ? images_collection[images_collection.length - 1] : null);

    upload_box.style.display = (event.target.checked ? 'inherit' : 'none');

    if (last_image)
      last_image.style.display = (event.target.checked ? 'none' : 'inherit');

    wall_grid_pagination_update();
  });

  var parsed_url = new URL(window.location.href);
  var query_match = parsed_url.search.match(/&offset=([0-9]+)&direction=(ASC|DESC)/);

  if (query_match) {
    var offset = query_match[1];
    var direction = query_match[2];
    wall_grid_update(offset, direction, 48);
  }
  else {
    // Update wall at page load.
    wall_grid_update(0, 'ASC', 48);

    var sync_interval;
    var sync_trigger = document.getElementById('sync_trigger');
    var post_trigger = document.getElementById('post_trigger');

    if (window.location.href.match(/&sync=true/)) {
      sync_trigger.checked = true;
      sync_interval = setInterval(function () {
        var offset = wall_grid_get_last_id();
        wall_grid_update(offset, 'ASC', 48);

      }, 3000);

      post_trigger.disabled = false;
    }
    else {
      clearInterval(sync_interval);

      if (post_trigger.checked || post_trigger.disabled == false) {
        post_trigger.checked = false;
        post_trigger.disabled = true;

        var post_trigger_event = document.createEvent("HTMLEvents");
        post_trigger_event.initEvent('change', true, false, { checked: false });
        post_trigger.dispatchEvent(post_trigger_event);
      }
    }
  }

})();
