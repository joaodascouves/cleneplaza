<div id="post-insert-container">
  <dl class="list-form">
    <form method="POST" enctype="multipart/form-data" id="insert_form">
        <dt>
          Image
        </dt>
        <dd>
          <input class="form" type="file" name="image_file" accept="image/*" />
        </dd>
        <dt>
          Title
        </dt>
        <dd>
          <input class="form" type="text" name="title" placeholder="Title" />
          <br/>
          <small>
            (keep empty to filename)
          </small>
        </dd>
        <dt>
          Text
        </dt>
        <dd>
          <textarea name="message" cols="55" rows="10" form="insert_form"></textarea>
        </dd>
        <dd>
          <input class="form-button" type="submit" value="Submit" />
        </dd>

    </form>
  <dl>
</div>
