<?php $unit=$this->unit_model->findbyid($edit_id); ?>
<?php echo form_open('define/update_unit/'.$edit_id,array('class' => 'form-horizontal')); ?>
  <div class="form-group">
        <label  class="col-sm-4 control-label">کاڵا :</label>
        <div class="col-sm-8">
          <select class="form-control" name='product_id' id='product_id' required>
            <option></option>
          <?php $product = $this->product_model->all();
              foreach ($product as $key) { 
                if ($unit->product_id == $key->id) { $selected = 'selected'; }
                echo "<option value='$key->id' $selected >$key->title</option>"  ;
                $selected='';
              }
           ?></select>
        </div>
      </div>
  <div class="form-group">
    <label class="col-sm-4 control-label">نێوی کاڵا :</label>
    <div class="col-sm-8">
      <input class="form-control" type="text" name="title" value="<?php echo $unit->title ?>" required>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-4 control-label">قیاسی یەکە :</label>
    <div class="col-sm-8">
      <input class="form-control" type="number" name="scale" value="<?php echo $unit->scale ?>" required>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-4 col-sm-8">
      <button class="btn btn-success" type="submit">پاشکەوت کردن</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?= anchor('define/unit','پاشگەز بۆنەوە',array('class'=>'btn btn-primary')); ?>
    </div>
  </div>
<?php echo form_close (); ?>