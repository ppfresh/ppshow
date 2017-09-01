{extend name="common:layout"}

{block name="content"}
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-create" data-toggle="tooltip" title="保存" class="btn btn-primary">
                    <i class="fa fa-save"></i>
                </button>
                <a href="{:url('index')}" data-toggle="tooltip" title="取消" class="btn btn-default">
                    <i class="fa fa-reply"></i>
                </a>
            </div>
            <h1>%title%管理</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="{:url('manage/dashboard')}">首页</a>
                </li>
                <li>
                    <a href="{:url('index')}">%title%管理</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-pencil"></i>
                    {if condition="is_null($id)"}添加{else/}编辑{/if}加%title%
                </h3>
            </div>
            <div class="panel-body">
                <form action="{:url('set',['id'=>$id])}" method="post" enctype="multipart/form-data" id="form-create" class="form-horizontal">
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab-general">
                            <!--隐藏域,传递id-->
                            {if condition="!is_null($id)"}
                            <input type="hidden" name="id" value="{$id}">
                            {/if}
                            %set-body-list%
                            
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="appendJs"}
<script type="text/javascript" src="__STATIC__/validate/jquery.validate.min.js"></script>
<script type="text/javascript" src="__STATIC__/validate/additional-methods.min.js"></script>
<script type="text/javascript" src="__STATIC__/validate/localization/messages_zh.min.js"></script>


<script>
    $('#form-create').validate({
        rules:{
            //根据情况定义验证规则
        },
        //内部的样式类,红色错误,可选的
        errorClass:"text-danger",

        messages:{
            
        }
    })



</script>
{/block}}