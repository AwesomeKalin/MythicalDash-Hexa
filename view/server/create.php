<?php
use MythicalDash\ErrorHandler;
use MythicalDash\SettingsManager;
use MythicalDash\EggManagerConfig;

include (__DIR__ . '/../requirements/page.php');
$csrf = new MythicalSystems\Utils\CSRFHandler;

use MythicalDash\Telemetry;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['createsv'])) {
    if ($csrf->validate('create-server-form')) {
      $queue = 0;
      $userdb = mysqli_query($conn, "SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'")->fetch_object();
      $ramLimit = $userdb->ram;
      $cpuLimit = $userdb->cpu;
      $diskLimit = $userdb->disk;
      $serverLimit = $userdb->server_limit;
      $usedRam = 0;
      $usedDatabase = 0;
      $usedPorts = 0;
      $usedDisk = 0;
      $usedBackups = 0;
      $usedCpu = 0;
      $servers = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
      $servers_in_queue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue WHERE ownerid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
      if ($servers_in_queue->num_rows >= 2) {
        header('location: /server/create?e=You cannot have more than two servers in queue.');
        $conn->close();
        die ();
      }
      foreach ($servers as $serv) {
        $ptid = $serv['pid'];
        $ch = curl_init(SettingsManager::getSetting('PterodactylURL') . '/api/application/servers/' . $ptid);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
          $ch,
          CURLOPT_HTTPHEADER,
          array(
            'Authorization: Bearer ' . SettingsManager::getSetting('PterodactylAPIKey'),
            'Content-Type: application/json',
            'Accept: application/json'
          )
        );
        $result1 = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result1, true);
        $ram = $result['attributes']['limits']['memory'];
        $disk = $result['attributes']['limits']['disk'];
        $ports = $result['attributes']['feature_limits']['allocations'] - 1;
        $databases = $result['attributes']['feature_limits']['databases'];
        $backups = $result['attributes']['feature_limits']['backups'];
        $cpuh = $result['attributes']['limits']['cpu'];
        $usedDatabase = $usedDatabase + $databases;
        $usedPorts = $usedPorts + $ports;
        $usedCpu = $usedCpu + $cpuh;
        $usedRam = $usedRam + $ram;
        $usedDisk = $usedDisk + $disk;
        $usedBackups = $usedBackups + $backups;
      }
      foreach ($servers_in_queue as $server) {
        $usedRam = $usedRam + $server['ram'];
        $usedDisk = $usedDisk + $server['disk'];
        $usedPorts = $usedPorts + $server['xtra_ports'];
        $usedDatabase = $usedDatabase + $server['databases'];
        $usedBackups = $usedBackups + $server['backuplimit'];
      }
      $freeRam = $ramLimit - $usedRam;
      $freeDisk = $diskLimit - $usedDisk;
      $freePorts = $userdb->ports - $usedPorts;
      $freeDatabases = $userdb->databases - $usedDatabase;
      $freeBackup = $userdb->backups - $usedBackups;
      $s_name = mysqli_real_escape_string($conn, $_POST['name']);
      $s_memory = mysqli_real_escape_string($conn, $_POST['memory']);
      $s_cores = mysqli_real_escape_string($conn, $_POST['cores']);
      $s_disk = mysqli_real_escape_string($conn, $_POST['disk']);
      $s_ports = mysqli_real_escape_string($conn, $_POST['ports']);
      $s_databases = mysqli_real_escape_string($conn, $_POST['databases']);
      $s_backups = mysqli_real_escape_string($conn, $_POST['backups']);
      $s_location = mysqli_real_escape_string($conn, $_POST['location']);
      $s_egg = mysqli_real_escape_string($conn, $_POST['egg']);
      if (!isset($s_name) || !isset($s_memory) || !isset($s_cores) || !isset($s_disk) || !isset($s_ports) || !isset($s_databases) || !isset($s_backups) || !isset($s_location) || !isset($s_egg)) {
        header('location: /server/create?e=' . $lang['please_fill_in_all_required_info']);
        $conn->close();
        die ();
      }
      if (!is_numeric($s_memory) || !is_numeric($s_disk) || !is_numeric($s_ports) || !is_numeric($s_databases) || !isset($s_backups) || !is_numeric($s_cores) || !is_numeric($s_location) || !is_numeric($s_egg)) {
        header('location: /server/create?e=' . $lang['invalid_field']);
        $conn->close();
        die ();
      }
      $usedServers = $servers->num_rows + $servers_in_queue->num_rows;
      if ($usedServers >= $serverLimit) {
        header('location: /server/create?e=' . str_replace('%PLACEHOLDER_1%', $lang['server_slot'], $lang['server_you_not_have']));
        $conn->close();
        die ();
      }
      if ($s_memory == 0 || $s_memory != round($s_memory, 0)) {
        header('location: /server/create?e=' . $lang['invalid_field']);
        $conn->close();
        die ();
      }
      if ($s_cores < 0.15) {
        header('location: /server/create?e=' . str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['cpu'], '0.10'), $lang['server_minimum_is']));
        $conn->close();
        die ();
      }
      if ($s_memory < 256) {
        header('location: /server/create?e=' . str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['ram'], '256MB'), $lang['server_minimum_is']));
        $conn->close();
        die ();
      }
      if ($s_disk < 256) {
        header('location: /server/create?e=' . str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['disk'], '256MB'), $lang['server_minimum_is']));
        $conn->close();
        die ();
      }
      if ($s_ports < 0 || $s_ports != round($s_ports, 0)) {
        header('location: /server/create?e=' . str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['server_allocation'], '0'), $lang['server_minimum_is']));
        $conn->close();
        die ();
      }
      if ($s_databases < 0 || $s_databases != round($s_databases, 0)) {
        header('location: /server/create?e=' . str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['mysql'], '0'), $lang['server_minimum_is']));
        $conn->close();
        die ();
      }
      if ($s_backups < 0 || $s_backups != round($s_backups, 0)) {
        header('location: /server/create?e=' . str_replace(array('%PLACEHOLDER_1%', '%PLACEHOLDER_2%'), array($lang['backup_slot'], '0'), $lang['server_minimum_is']));
        $conn->close();
        die ();
      }
      if ($s_cores > $cpuLimit) {
        header('location: /server/create?e=' . str_replace('%PLACEHOLDER_1%', $lang['cpu'], $lang['server_you_not_have']));
        $conn->close();
        die ();
      }
      if ($s_memory > $freeRam) {
        header('location: /server/create?e=' . str_replace('%PLACEHOLDER_1%', $lang['ram'], $lang['server_you_not_have']));
        $conn->close();
        die ();
      }
      if ($s_disk > $freeDisk) {
        header('location: /server/create?e=' . str_replace('%PLACEHOLDER_1%', $lang['disk'], $lang['server_you_not_have']));
        $conn->close();
        die ();
      }
      if ($s_ports > $freePorts) {
        header('location: /server/create?e=' . str_replace('%PLACEHOLDER_1%', $lang['server_allocations'], $lang['server_you_not_have']));
        $conn->close();
        die ();
      }
      if ($s_databases > $freeDatabases) {
        header('location: /server/create?e=' . str_replace('%PLACEHOLDER_1%', $lang['mysql'], $lang['server_you_not_have']));
        $conn->close();
        die ();
      }
      if ($s_backups > $freeBackup) {
        header('location: /server/create?e=' . str_replace('%PLACEHOLDER_1%', $lang['backup_slot'], $lang['server_you_not_have']));
        $conn->close();
        die ();
      }
      $locid = $s_location;
      $doeslocationexist = mysqli_query($conn, "SELECT * FROM mythicaldash_locations WHERE id = '" . mysqli_real_escape_string($conn, $locid) . "'");
      if ($doeslocationexist->num_rows == 0) {
        header('location: /server/create?e=' . $lang['server_location_does_not_exist']);
        $conn->close();
        die ();
      }
      $doeseggexist = mysqli_query($conn, "SELECT * FROM mythicaldash_eggs where id = '" . mysqli_real_escape_string($conn, $s_egg) . "'");
      if ($doeseggexist->num_rows == 0) {
        header('location: /server/create?e=' . $lang['server_egg_does_not_exist']);
        $conn->close();
        die ();
      }
      $egg = $doeseggexist->fetch_object();
      if (EggManagerConfig::isPremiumOnly($s_egg) && !in_array($userdb->role, ['User,Premium', 'Support', 'Administrator'])) {
        header('location: /server/create?e=This egg is limited to Premium users, Support, and Administrators only.');
        $conn->close();
        die ();
      }
      $conn->query("INSERT INTO mythicaldash_servers_queue (`name`, `ram`, `disk`, `cpu`, `xtra_ports`, `databases`, `backuplimit`, `location`, `ownerid`, `type`, `egg`, `puid`
      ) VALUES (
        '" . mysqli_real_escape_string($conn, $s_name) . "',
        '" . mysqli_real_escape_string($conn, $s_memory) . "', 
        '" . mysqli_real_escape_string($conn, $s_disk) . "', 
        '" . mysqli_real_escape_string($conn, $s_cores) . "', 
        '" . mysqli_real_escape_string($conn, $s_ports) . "', 
        '" . mysqli_real_escape_string($conn, $s_databases) . "', 
        '" . mysqli_real_escape_string($conn, $s_backups) . "', 
        '" . mysqli_real_escape_string($conn, $s_location) . "', 
        '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "', 
        '$queue', 
        '" . $s_egg . "', 
        '$userdb->panel_id')");
      Telemetry::NewServer();
      header('location: /dashboard?s=' . str_replace('%PLACEHOLDER_1%', SettingsManager::getSetting('name'), $lang['server_created']));
      die ();
    } else {
      header('location: /server/create?e=' . $lang['csrf_failed']);
      die ();
    }
  }
} else {
  $nuserdb = $conn->query("SELECT * FROM mythicaldash_users WHERE api_key = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'")->fetch_array();
  $servers = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE uid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
  $servers_in_queue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue WHERE ownerid = '" . mysqli_real_escape_string($conn, $_COOKIE['token']) . "'");
  $serversnumber = $servers->num_rows + $servers_in_queue->num_rows;
  $usedRam = 0;
  $usedDisk = 0;
  $usedCpu = 0;
  $usedPorts = 0;
  $usedDatabase = 0;
  $usedBackup = 0;
  $uservers = array();
  foreach ($servers as $serv) {
    $ptid = $serv['pid'];
    $ch = curl_init(SettingsManager::getSetting('PterodactylURL') . '/api/application/servers/' . $ptid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(
      $ch,
      CURLOPT_HTTPHEADER,
      array(
        'Authorization: Bearer ' . SettingsManager::getSetting('PterodactylAPIKey'),
        'Content-Type: application/json',
        'Accept: Application/vnd.pterodactyl.v1+json'
      )
    );
    $result1 = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpcode != 200) {
      ErrorHandler::ShowCritical($lang['pterodactyl_connection_error']);
    }
    curl_close($ch);
    $result = json_decode($result1, true);
    $id = $result['attributes']['uuid'];
    $name = $result['attributes']['name'];
    $ram = $result['attributes']['limits']['memory'];
    $disk = $result['attributes']['limits']['disk'];
    $cpuh = $result['attributes']['limits']['cpu'];
    $db = $result['attributes']['feature_limits']['databases'];
    $usedRam = $usedRam + $ram;
    $usedDisk = $usedDisk + $disk;
    $alloc = $result['attributes']['feature_limits']['allocations'] - 1;
    $usedBackup = $result['attributes']['feature_limits']['backups'];
    $usedPorts = $usedPorts + $alloc;
    $usedDatabase = $usedDatabase + $db;
    $usedCpu = $usedCpu + $cpuh;
    array_push($uservers, $result['attributes']);
  }
  foreach ($servers_in_queue as $server) {
    $usedRam = $usedRam + $server['ram'];
    $usedDisk = $usedDisk + $server['disk'];
    $usedPorts = $usedPorts + $server['xtra_ports'];
    $usedBackup = $usedBackup + $server['backuplimit'];
    $usedDatabase = $usedDatabase + $server['databases'];
    $usedCpu = $usedCpu + $server['cpu'];
  }
  try {
    $freeram = $nuserdb['ram'] - $usedRam;
    $freedisk = $nuserdb['disk'] - $usedDisk;
    $freecpu = $nuserdb['cpu'] - $usedCpu;
    $freesvslots = $nuserdb['server_limit'] - $serversnumber;
    $freebackups = $nuserdb['backups'] - $usedBackup;
    $freeports = $nuserdb['ports'] - $usedPorts;
    $freedbs = $nuserdb['databases'] - $usedDatabase;
  } catch (Exception $ex) {
    header('location: /server/create?e=' . $lang['login_error_unknown'] . ': <code>' . $ex->getMessage() . '</code>');
    $conn->close();
    die ();
  }
}
?>
<!DOCTYPE html>

<html lang="en" class="dark-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
  data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <?php include (__DIR__ . '/../requirements/head.php'); ?>
  <title>
    <?= SettingsManager::getSetting('name') ?> - <?= $lang['create_server'] ?>
  </title>
  <link rel="stylesheet" href="<?= $appURL ?>/assets/vendor/css/pages/page-help-center.css" />
</head>

<body>
<?php
if (SettingsManager::getSetting('show_snow') == 'true') {
  include (__DIR__ . '/../components/snow.php');
}
?>
  <div id="preloader" class="discord-preloader">
    <div class="spinner"></div>
  </div>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <?php include (__DIR__ . '/../components/sidebar.php') ?>
      <div class="layout-page">
        <?php include (__DIR__ . '/../components/navbar.php') ?>
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><?= $lang['server'] ?> / </span> <?= $lang['create_server'] ?></h4>
            <?php include (__DIR__ . '/../components/alert.php') ?>
            <div id="ads">
              <?php
                if (SettingsManager::getSetting('enable_ads') == 'true') {
              ?>
                <br>
                <?= SettingsManager::getSetting('ads_code') ?>
                <br>
                <?php
                }
                                ?>
            </div>
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title"><?= $lang['create_server'] ?></div>
                </div>
                <div class="card-body">
                  <form method="POST">
                    <label for="name"><?= $lang['name'] ?>:</label>
                    <input type="text" name="name" class="form-control" id="name" required placeholder="<?= $session->getUserInfo('username') ?>'s server">
                    <br>
                    <?php
                      $locations = mysqli_query($conn, 'SELECT * FROM mythicaldash_locations')->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <label for="location"><?= $lang['location'] ?>:</label>
                    <select class="form-control" name="location" required id="location">
                      <?php foreach ($locations as $location): ?>
                        <?php
                        $serversOnLoc = mysqli_query($conn, "SELECT * FROM mythicaldash_servers WHERE location='" . mysqli_real_escape_string($conn, $location['id']) . "'")->fetch_all(MYSQLI_ASSOC);
                        $serversInQueue = mysqli_query($conn, "SELECT * FROM mythicaldash_servers_queue WHERE location='" . mysqli_real_escape_string($conn, $location['id']) . "'")->fetch_all(MYSQLI_ASSOC);
                        $availableSlots = $location['slots'] - count($serversOnLoc) - count($serversInQueue);
                        ?>
                        <option value="<?= $location['id'] ?>">
                          <?= $location['name'] ?> (
                          <?= $availableSlots ?>/
                          <?= $location['slots'] ?> <?= $lang['slots'] ?>) 
                        </option>
                      <?php endforeach; ?>
                    </select>

                    <?php if (count($locations) == 0): ?>
                      <p><?= $lang['server_no_nodes'] ?></p>
                    <?php endif; ?>
                    <br>
                    <label for="egg"><?= $lang['egg'] ?>:</label>
                    <select class="form-control" name="egg" required id="egg">
                      <?php
                        $alrCategories = array();
                        $eggs = mysqli_query($conn, 'SELECT * FROM mythicaldash_eggs')->fetch_all(MYSQLI_ASSOC);
                        foreach ($eggs as $egg) {
                          $category = $egg['category'];
                          if (in_array($category, $alrCategories)) {
                            continue;
                          }
                          array_push($alrCategories, $category);
                          echo '<optgroup label="' . $category . '">';
                          $categoryEggs = array_filter($eggs, function ($e) use ($category) {
                            return $e['category'] === $category;
                          });
                          foreach ($categoryEggs as $categoryEgg) {
                            echo '<option value="' . $categoryEgg['id'] . '">' . $categoryEgg['name'] . '</option>';
                          }
                          echo '</optgroup>';
                        }
                      ?>
                    </select>
                    <br>
                    <label for="memory"><?= $lang['ram'] ?>:</label>
                    <input type="number" name="memory" class="form-control" id="ram" value="<?= $freeram ?>" required
                      placeholder="<?= $freeram ?>">
                    <br>
                    <label for="disk"><?= $lang['disk'] ?>:</label>
                    <input type="number" name="disk" class="form-control" id="disk" required value="<?= $freedisk ?>" placeholder="<?= $freedisk ?>">
                    <br>
                    <label for="cpu"><?= $lang['cpu'] ?>:</label>
                    <input type="number" name="cores" class="form-control" id="cpu" required value="<?= $freecpu ?>"  placeholder="<?= $freecpu ?>">
                    <br>
                    <label for="allocations"><?= $lang['server_allocation'] ?>:</label>
                    <input type="number" name="ports" class="form-control" id="allocations" required
                      placeholder="<?= $freeports ?>" value="<?= $freeports ?>">
                    <br>
                    <label for="databases"><?= $lang['mysql'] ?>:</label>
                    <input type="number" name="databases" class="form-control" id="databases" required
                      placeholder="<?= $freedbs ?>" value="<?= $freedbs ?>">
                    <br>
                    <label for="backups"><?= $lang['backup_slot'] ?>:</label>
                    <input type="number" name="backups" class="form-control" id="backups" required
                      placeholder="<?= $freebackups ?>" value="<?= $freebackups ?>">
                    <br>
                    <?= $csrf->input('create-server-form'); ?>
                    <button action="submit" name="createsv" class="btn btn-primary"><?= $lang['create_server'] ?></button>
                    <br>
                </div>
                </form>
              </div>
            </div>
            <div id="ads">
              <?php
                if (SettingsManager::getSetting('enable_ads') == 'true') {
              ?>
                <br>
                <?= SettingsManager::getSetting('ads_code') ?>
                <br>
                <?php
                }
                                ?>
            </div>
          </div>
        </div>
        <?php include (__DIR__ . '/../components/footer.php') ?>
        <div class="content-backdrop fade"></div>
      </div>
    </div>
  </div>
  <div class="layout-overlay layout-menu-toggle"></div>
  <div class="drag-target"></div>
  </div>
  <?php include (__DIR__ . '/../requirements/footer.php') ?>
  <script src="<?= $appURL ?>/assets/js/dashboards-ecommerce.js"></script>
  <script>
    function testPing(host) {
        var pingElement = document.getElementById('ping_' + host);

        if (pingElement) {
            fetch('<?= $appURL?>/ping.php?host=' + host)
                .then(response => response.json())
                .then(data => {
                    pingElement.innerText = data.ping + 'ms';
                })
                .catch(error => {
                    console.error('Error fetching ping:', error);
                });
        } else {
            console.error('Ping element not found for host:', host);
        }
    }

    document.getElementById('location').addEventListener('change', function () {
        var selectedOption = this.options[this.selectedIndex];
        var host = selectedOption.getAttribute('data-host');
        testPing(host);
    });
</script>


</body>

</html>
