<h2>{'Register'|@translate}</h2>

<form method="post">
  <fieldset>
    <legend>{'Personnal informations'|@translate}</legend>

    <table>
      <tr>
        <th>{'Username'|@translate}</th>
        <td><input type="text" name="username" /></td>
      </tr>
      <tr>
        <th>{'Password'|@translate}</th>
        <td><input type="password" name="password" /></td>
      </tr>
      <tr>
        <th>{'Confirm password'|@translate}</th>
        <td><input type="password" name="confirmation_password" /></td>
      </tr>
      <tr>
        <th>{'Mail address'|@translate}</th>
        <td><input type="text" name="email" /></td>
      </tr>
    </table>
    <script type= "text/javascript">  
    var RecaptchaOptions = {literal}{{/literal} 
				lang : '{$lang}',  
				theme : '{$theme}',  
				{if $custom_translation }
				 custom_translations : {literal}{{/literal} 
				 								instructions_visual : "{'instructions_visual'|@translate}",
                        instructions_audio : "{'instructions_audio'|@translate}",
                        play_again : "{'play_again'|@translate}",
                        cant_hear_this : "{'cant_hear_this'|@translate}",
                        visual_challenge : "{'visual_challenge'|@translate}",
                        audio_challenge : "{'audio_challenge'|@translate}",
                        refresh_btn : "{'refresh_btn'|@translate}",
                        help_btn : "{'help_btn'|@translate}",
                        incorrect_try_again : "{'incorrect_try_again'|@translate}",
											{literal}},{/literal} 
				{/if}
    {literal}};  {/literal}
    </script>  
    {$html_recaptcha}
    <div>
      <input type="submit" name="submit" value="{'Submit'|@translate}" />
    </div>

  </fieldset>
</form>