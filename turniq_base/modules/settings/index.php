<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
requirePermission('settings');

$db=$getDB=getDB();
$msg='';$msg_type='';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    if ($_POST['action']==='save_business') {
        foreach(['business_name','business_phone','business_address','business_email'] as $k){
            $v=$db->real_escape_string(trim($_POST[$k]??''));
            $db->query("INSERT INTO settings(setting_key,setting_val) VALUES('$k','$v') ON DUPLICATE KEY UPDATE setting_val='$v'");
        }
        unset($_SESSION['business_name']);
        header('Location: index.php?saved=1');exit;
    }
    if ($_POST['action']==='change_password') {
        $uid=$_SESSION['user_id'];
        $user=$db->query("SELECT password_hash FROM users WHERE id=$uid")->fetch_assoc();
        $cur=$_POST['current_password']??'';$new=$_POST['new_password']??'';$con=$_POST['confirm_password']??'';
        if (!password_verify($cur,$user['password_hash'])){$msg='Contraseña actual incorrecta.';$msg_type='error';}
        elseif(strlen($new)<6){$msg='Mínimo 6 caracteres.';$msg_type='error';}
        elseif($new!==$con){$msg='Las contraseñas no coinciden.';$msg_type='error';}
        else{$hash=password_hash($new,PASSWORD_DEFAULT);$db->query("UPDATE users SET password_hash='$hash' WHERE id=$uid");$msg='✅ Contraseña actualizada.';$msg_type='success';}
    }
    if ($_POST['action']==='save_theme') {
        $color=$db->real_escape_string($_POST['theme_color']??'#7B2FBE');
        $db->query("INSERT INTO settings(setting_key,setting_val) VALUES('theme_color','$color') ON DUPLICATE KEY UPDATE setting_val='$color'");
        $msg='✅ Color actualizado. Recarga para verlo.';$msg_type='success';
    }
}

$s=[];$r=$db->query("SELECT setting_key,setting_val FROM settings");
while($row=$r->fetch_assoc())$s[$row['setting_key']]=$row['setting_val'];
$uid=$_SESSION['user_id'];
$user=$db->query("SELECT name,email,role FROM users WHERE id=$uid")->fetch_assoc();
$db->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración — <?= BUSINESS_NAME ?></title>
    <?php include '../../ui/layout/head.php'; ?>
</head>
<body>
<?php include '../../ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../ui/layout/topbar.php'; ?>
    <div class="page-body">
        <h1 class="page-title">Configuración</h1>
        <p class="page-subtitle">Personaliza tu sistema Turniq</p>

        <?php if(isset($_GET['saved'])): ?><div class="alert alert-success">✅ Configuración guardada correctamente.</div><?php endif; ?>
        <?php if($msg): ?><div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

            <div class="section-card">
                <div class="section-header"><h2>🏢 Datos del negocio</h2></div>
                <form method="POST">
                    <input type="hidden" name="action" value="save_business">
                    <div class="form-group"><label>Nombre del negocio</label><input type="text" name="business_name" value="<?= htmlspecialchars($s['business_name']??'') ?>" placeholder="Mi Negocio"></div>
                    <div class="form-row">
                        <div class="form-group"><label>Teléfono</label><input type="text" name="business_phone" value="<?= htmlspecialchars($s['business_phone']??'') ?>" placeholder="555-0000"></div>
                        <div class="form-group"><label>Email</label><input type="email" name="business_email" value="<?= htmlspecialchars($s['business_email']??'') ?>" placeholder="negocio@email.com"></div>
                    </div>
                    <div class="form-group"><label>Dirección</label><textarea name="business_address" rows="2" placeholder="Calle, colonia, ciudad..."><?= htmlspecialchars($s['business_address']??'') ?></textarea><div class="form-hint">Aparece en los recibos</div></div>
                    <button type="submit" class="btn-primary">Guardar datos</button>
                </form>
            </div>

            <div class="section-card">
                <div class="section-header"><h2>🔒 Cambiar contraseña</h2></div>
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px">Usuario: <strong><?= htmlspecialchars($user['name']) ?></strong></p>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group"><label>Contraseña actual</label><input type="password" name="current_password" required></div>
                    <div class="form-group"><label>Nueva contraseña</label><input type="password" name="new_password" placeholder="Mínimo 6 caracteres" required></div>
                    <div class="form-group"><label>Confirmar nueva</label><input type="password" name="confirm_password" required></div>
                    <button type="submit" class="btn-primary">Cambiar contraseña</button>
                </form>
            </div>

            <div class="section-card">
                <div class="section-header"><h2>🎨 Color del sistema</h2></div>
                <form method="POST">
                    <input type="hidden" name="action" value="save_theme">
                    <div class="form-group">
                        <label>Color principal</label>
                        <div style="display:flex;gap:10px;align-items:center">
                            <input type="color" name="theme_color" value="<?= htmlspecialchars($s['theme_color']??'#7B2FBE') ?>" style="width:56px;height:40px;border:1px solid var(--border);border-radius:8px;cursor:pointer;padding:2px;background:var(--bg-input)" oninput="document.getElementById('hexVal').value=this.value">
                            <input type="text" id="hexVal" value="<?= htmlspecialchars($s['theme_color']??'#7B2FBE') ?>" style="width:110px" oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value))document.querySelector('input[type=color]').value=this.value">
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
                        <?php foreach(['#7B2FBE','#CC0000','#1D4ED8','#15803D','#B45309','#0F766E','#111111'] as $c): ?>
                        <div onclick="document.querySelector('input[type=color]').value='<?= $c ?>';document.getElementById('hexVal').value='<?= $c ?>'" style="width:30px;height:30px;border-radius:8px;background:<?= $c ?>;cursor:pointer;border:2px solid transparent;transition:all 0.15s" title="<?= $c ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn-primary">Aplicar color</button>
                </form>
            </div>

            <div class="section-card">
                <div class="section-header"><h2>💾 Respaldo de datos</h2></div>
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px">Exporta una copia de seguridad de todos los datos. Guárdala en un lugar seguro.</p>
                <div class="alert alert-warning" style="margin-bottom:16px">⚠️ Se recomienda hacer un respaldo semanal.</div>
                <a href="backup.php" target="_blank" class="btn-primary">💾 Descargar respaldo</a>
                <p style="font-size:12px;color:var(--text-dim);margin-top:8px">turniq_backup_<?= date('Y-m-d') ?>.sql</p>
            </div>

        </div>
    </div>
</div>
</body>
</html>