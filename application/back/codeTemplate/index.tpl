{extend name="common:layout"}

{block name="content"}
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <a href="{:url('set')}" data-toggle="tooltip" title="新增" class="btn btn-primary"> <i class="fa fa-plus"></i>
                </a>
                <button type="button" data-toggle="tooltip" title="删除" id="button_batch" class="btn btn-danger" onclick="confirm('确定删除?')? $('#form-index').submit():'flase'">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
            <h1>%title%管理</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="">首页</a>
                </li>
                <li>
                    <a href="">%title%管理</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-list"></i>
                    %title%列表
                </h3>
            </div>
            <div class="panel-body">
                <!-- <form action="{:url('index')}" method="get" >
                <div class="well">
                    <div class="row">
                         <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label" for="input-filter_title">%title%名称</label>
                                <input type="text" name="filter_title" value="{$filter_title|default=''}" placeholder="%title%名称" id="input-filter_title" class="form-control" />
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="input-price">销售价格</label>
                                <input type="text" name="filter_price" value="" placeholder="销售价格" id="input-price" class="form-control" />
                            </div>
                         </div>
                         <div class="col-sm-12"> 
                            <div class="form-group">
                                <label class="control-label" for="input-status">状态</label>
                                <select name="filter_status" id="input-status" class="form-control">
                                    <option value="*"></option>
                                    <option value="1">启用</option>
                                    <option value="0">停用</option>
                                </select>
                            </div>
                            <button type="submit" id="button-filter" class="btn btn-primary pull-right">
                                <i class="fa fa-search"></i>
                                筛选
                            </button>
                        </div> 
                    </div>
                </div>
                </form> -->
                <form action="{:url('batch')}" method="post" id="form-index">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <td style="width: 1px;" class="text-center">
                                    <input type="checkbox" id="select_all" />
                                </td>
                                
                               %index-head-list%
                                

                                <td class="text-right">管理</td>
                            </tr>
                            </thead>
                            <tbody>
                            {volist name="rows" id="vol"}
                            <tr>
                                <td class="text-center">
                                <input type="checkbox" name="selected[]" value="{$vol['id']}" />
                                </td>
                                
                                %index-body-list%

                                <td class="text-right">
                                    <a href="{:url('set',['id'=>$vol['id']])}" data-toggle="tooltip" title="编辑" class="btn btn-primary">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <!--<a href="{:url('Back/Goods/option')}" data-toggle="tooltip" title="管理选项" class="btn btn-primary">-->
                                        <!--<i class="fa">O</i>-->
                                    <!--</a>-->
                                    <!-- <a href="{:url('Back/Goods/copy')}" data-toggle="tooltip" title="复制" class="btn btn-primary">
                                      <i class="fa fa-copy"></i>
                                    </a> -->
                                </td>
                            </tr>
                            {/volist}

                            </tbody>
                        </table>
                    </div>
                </form>
                <!-- <div class="row">
                    <div class="col-sm-6 text-left">
                        {$rows->render()}
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
{/block}

{block name="appendJs"}
<script>
    $(function(){
        $('#select_all').click(function(){
            $(":checkbox[name='selected[]']").prop('checked',$(this).prop('checked'));
        })
    })
</script>
{/block}