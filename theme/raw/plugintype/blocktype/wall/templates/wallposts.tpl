{include file="header.tpl"}

    <h2>{$displayname}: {str tag='wall' section='blocktype.wall'}</h2>

    <a href="{profile_url($owner)}" class="btn btn-secondary">
        <span class="icon icon-arrow-left left" role="presentation" aria-hidden="true"></span>
        {str tag='backtoprofile' section='blocktype.wall'}
    </a>

    <div class="row">
        <div class="col-md-8">
            {include file="blocktype:wall:inlineposts.tpl"}
        </div>
    </div>

{include file="footer.tpl"}
