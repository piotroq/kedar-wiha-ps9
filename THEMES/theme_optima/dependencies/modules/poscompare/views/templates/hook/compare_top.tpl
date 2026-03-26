<div class="compare-top">
	<a href="{if $compare_top_link} {$compare_top_link} {/if}">
		{if isset($icon) && $icon}
			<i class="{$icon}"></i> 
		{/if}		 
		<span class="compare-top-text">{l s='Compare' mod='poscompare'}</span>
		<span class="compare-top-count"><span id="poscompare-nb">0</span></span>
	</a>
</div>
<script type="text/javascript">
var baseDir ='{$content_dir}'; 
</script>
