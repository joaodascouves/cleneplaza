<div class="post-container" id="comment-box-container">
  <dl class="list-form">
    <form action="?context=comment" method="POST" enctype="multipart/form-data" id="comment_form">
      <input type="hidden" name="context" value="<? echo $_GET['context']; ?>" />
      <input type="hidden" name="entry_id" value="<? echo $_GET['entry_id']; ?>" />
      <dt>
        Message
      </dt>
      <dd>
        <textarea name="message" cols="55" rows="10" form="comment_form"></textarea>
      </dd>
      <dd>
        <input type="file" name="image_file" accept="image/*" />
      </dd>
      <dd>
        <input type="submit" value="Submit" class="form-button" />
      </dd>
    </form>
  </dl>
</div>
