<h2>{'Login'|@translate}</h2>

<form method="post">
  <fieldset>
    <legend>{'Connection settings'|@translate}</legend>

    <table>
      <tr>
        <th>{'Username'|@translate}</th>
        <td><input type="text" name="username" /></td>
      </tr>
      <tr>
        <th>{'Password'|@translate}</th>
        <td><input type="password" name="password" /></td>
      </tr>
    </table>

    <div>
      <input type="submit" name="submit" value="{'Submit'|@translate}" />
    </div>

    <p>{'No account?'|@translate} <a href="register.php" {if isset($external_register) and $external_register}target="_blank"{/if}>{'Register'|@translate}</a></p>

  </fieldset>
</form>