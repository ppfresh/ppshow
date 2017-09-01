<div class="form-group %required%">
 <label class="col-sm-2 control-label" for="input-%field_name%">%field_title%</label>
   <div class="col-sm-10">
     <input type="text" name="%field_name%" value="{$data['%field_name%']|default=''}" placeholder="%field_title%" id="input-%field_name%" class="form-control" />
       {if condition="isset($errorInfo['%field_name%'])"}
      <label for="%field_name%" class="text-danger">{$errorInfo['%field_name%']}
      </label>
       {/if}
 </div>
</div>