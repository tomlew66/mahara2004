{if $list}
<p>{$stryouhavecollections}</p>
<ul>
{foreach from=$list item=item}
  {if $item.folder}
    <li><a href="views/{$item.folder}/index.html">{$item.title}</a></li>
  {elseif $item.views}
    <li>{$item.title}
      <ul>
      {if $item.progresscompletion}
        <li><a href="views/{$item.progresscompletionfolder}/index.html">{str tag="progresscompletion" section="admin"}</a></li>
      {/if}
    {foreach from=$item.views item=view}
        <li><a href="views/{$view.folder}/index.html">{$view.title}</a></li>
    {/foreach}
      </ul>
    </li>
  {/if}
{/foreach}
</ul>
{else}
<p>{str tag=youhavenocollections section=collection}</p>
{/if}
