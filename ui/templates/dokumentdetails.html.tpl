{include file="header.html.tpl" ansicht="Dokument anzeigen"}
<p class="pagetitle">Dokument {$dokument.label}</p>

<div>
<iframe style="float:left; width:50%;" src="{"dokumente_view"|___:$dokument.dokumentid}" height="300px"></iframe>

<div style="float:right; width:45%;">
{include file="dokumentform.block.tpl" dokument=$dokument dokumentkategorien=$dokumentkategorien dokumentstatuslist=$dokumentstatuslist}
</div>

<div style="clear:both;"></div>

<div style="float:left; width:40%;">
<div class="buttonbox">
 <a href="{"dokumente_mitglied"|___:$dokument.dokumentid}">Mitglied verlinken</a>
 <form action="{"dokumente_mitglied_create"|___:$dokument.dokumentid}" method="post" class="filter">
  <fieldset>
   <select name="mitgliedtemplateid">
    <option value="">{"(keine Vorlage)"|__}</option>
    {foreach from=$mitgliedtemplates item=mitgliedtemplate}<option value="{$mitgliedtemplate.mitgliedtemplateid|escape:html}" {if $dokument.data.mitgliedtemplateid == $mitgliedtemplate.mitgliedtemplateid}selected="selected"{/if}>{$mitgliedtemplate.label|escape:html}</option>{/foreach}
   </select>
   <input type="submit" value="{"Mitglied anlegen"|__}" />
  </fieldset>
 </form>
</div>
{if count($mitglieder) > 0}
{include file="mitgliederlist.block.tpl" mitglieder=$mitglieder showmitglieddokumentdel=1}
<div class="buttonbox">
 <a href="{"dokumente_mitglied"|___:$dokument.dokumentid}">Mitglied verlinken</a>
 <form action="{"dokumente_mitglied_create"|___:$dokument.dokumentid}" method="post" class="filter">
  <fieldset>
   <select name="mitgliedtemplateid">
    <option value="">{"(keine Vorlage)"|__}</option>
    {foreach from=$mitgliedtemplates item=mitgliedtemplate}<option value="{$mitgliedtemplate.mitgliedtemplateid|escape:html}" {if $dokument.data.mitgliedtemplateid == $mitgliedtemplate.mitgliedtemplateid}selected="selected"{/if}>{$mitgliedtemplate.label|escape:html}</option>{/foreach}
   </select>
   <input type="submit" value="{"Mitglied anlegen"|__}" />
  </fieldset>
 </form>
</div>
{/if}
</div>

<div style="float:right; width:55%;">
{foreach from=$dokumentnotizen item=notiz}
<div class="notiz">
 <span class="meta">{"Von %s"|__:$notiz.author.username}</span>
 {if isset($notiz.nextkategorie)}<span class="nextkategorie">{"Unter %s abgelegt"|__:$notiz.nextkategorie.label}</span>{/if}
 {if isset($notiz.nextstatus)}<span class="nextstatus">{"Als %s markiert"|__:$notiz.nextstatus.label}</span>{/if}
 <div class="kommentar">{$notiz.kommentar}</div>
</div>
{/foreach}
</div>

<div style="clear:both;"></div>
</div>
{include file="footer.html.tpl"}
