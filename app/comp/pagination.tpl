<!--{ Module : Pagination }-->
<div class="pagination">
Page {$pageNumber} sur {$pageCount}<br />
{?( {$pageCount} > 1 )?}{?( {$pageNumber} > 1 )?}<a href="{$baseLink}&amp;p=(# {$pageNumber} - 1 #)">&laquo;</a>{/?}
<span class="pagenums">
[:1,{$pageCount}:]
    {?({$value} != {$pageNumber})?}
        <a href="{$baseLink}&amp;p=(# {$value} #)" class="p">{$value}</a>
    @else@
        <span class="p act">{$value}</span>
    {/?}
    {?({$value} < {$pageCount})?}<span class="sep">-</span>{/?}
[/]
</span>{/?}

{?({$pageNumber} < {$pageCount})?}
<a href="{$baseLink}&amp;p=(# {$pageNumber} + 1 #)">&raquo;</a>
{/?}
</div>
