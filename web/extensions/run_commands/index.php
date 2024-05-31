<?php
use OpenPanel\core\cli\CommandLine;

function page() {
  ?>
  <h2>
    <a href="/">
      < Main page
    </a>
  </h2>
  <div>
    <h1>Run commands</h1>
    <form method="post">
      <select name="script">
        <?php
        $scripts = CommandLine::list();
        foreach ($scripts as $script) {
          ?>
          <option value="<?= $script ?>"><?= $script ?></option>
          <?php
        }
        ?>
      </select>
      <input type="text" name="args" placeholder="Arguments">
      <button type="submit">Run</button>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $script = $_POST['script'];
      $args = $_POST['args'];
      $output = CommandLine::exec($script, explode(' ', $args));
      ?>
      <h2>Output</h2>
      <pre>
        <?php
        foreach ($output['output'] as $line) {
          echo $line . "\n";
        }
        ?>
      </pre>
      <h2>Return code</h2>
      <pre>
        <?= $output['return'] ?>
      </pre>
      <?php
    }
    ?>
  </div>
  <?php
}