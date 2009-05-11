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

    <div>
      <input type="submit" name="submit" value="{'Submit'|@translate}" />
    </div>

  </fieldset>
</form>