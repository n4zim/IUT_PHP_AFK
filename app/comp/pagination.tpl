<!--{ Module : Pagination }-->
<div class="pagination">
Page {$page} sur {$pageCount}<br />

{?( {$page} > 1 )?}
<a href="{$baseLink}&amp;p=(# {$page} - 1 #)">&laquo;</a>
{/?}

<span class="pagenums">
[:1,{$pageCount}:]
    {?({$value} != {$page})?}
        <a href="{$baseLink}&amp;p=(# {$value} #)" class="p">{$value}</a>
    @else@
        <span class="p act">{$value}</span>
    {/?}
    
    {?({$value} < {$pageCount})?}<span class="sep">-</span>{/?}
[/]
</span>

{?({$page} < {$pageCount})?}
<a href="{$baseLink}&amp;p=(# {$page} + 1 #)">&raquo;</a>
{/?}
</div>
