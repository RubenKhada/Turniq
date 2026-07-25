<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
if($_SESSION['user_role']!=='admin'){redirect('dashboard.php');}

$db=$getDB=getDB();$msg='';$msg_type='';

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])){
    if($_POST['action']==='create'){
        $name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$role=$_POST['role']??'cashier';$pass=$_POST['password']??'';$by=$_SESSION['user_id'];
        if(!$name||!$email||!$pass){$msg='Todos los campos son obligatorios.';$msg_type='error';}
        elseif(strlen($pass)<6){$msg='Mínimo 6 caracteres.';$msg_type='error';}
        elseif($db->query("SELECT id FROM users WHERE email='".$db->real_escape_string($email)."'")->fetch_assoc()){$msg='Email ya registrado.';$msg_type='error';}
        else{$hash=password_hash($pass,PASSWORD_DEFAULT);$stmt=$db->prepare("INSERT INTO users(name,email,password_hash,role,created_by) VALUES(?,?,?,?,?)");$stmt->bind_param("ssssi",$name,$email,$hash,$role,$by);$stmt->execute();$msg='✅ Usuario creado.';$msg_type='success';}
    }
    if($_POST['action']==='toggle'){$id=intval($_POST['id']);$st=intval($_POST['status']);if($id===$_SESSION['user_id']){$msg='No puedes desactivarte.';$msg_type='error';}else{$db->query("UPDATE users SET is_active=$st WHERE id=$id");$msg=$st?'✅ Activado.':'Usuario desactivado.';$msg_type='success';}}
    if($_POST['action']==='reset'){$id=intval($_POST['id']);$pass=$_POST['new_password']??'';if(strlen($pass)<6){$msg='Mínimo 6 caracteres.';$msg_type='error';}else{$hash=password_hash($pass,PASSWORD_DEFAULT);$db->query("UPDATE users SET password_hash='$hash' WHERE id=$id");$msg='✅ Contraseña actualizada.';$msg_type='success';}}
    if($_POST['action']==='role'){$id=intval($_POST['id']);$role=$_POST['role']??'cashier';if($id===1){$msg='No puedes cambiar el admin principal.';$msg_type='error';}else{$db->query("UPDATE users SET role='$role' WHERE id=$id");$msg='✅ Rol actualizado.';$msg_type='success';}}
}

$users=$db->query("SELECT u.*,c.name as creator FROM users u LEFT JOIN users c ON u.created_by=c.id ORDER BY u.is_active DESC,u.id");
$db->close();
$roles=['admin'=>['Admin','badge-red'],'cashier'=>['Cajero','badge-blue'],'receptionist'=>['Recepcionista','badge-green'],'viewer'=>['Solo lectura','badge-gray']];
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Usuarios — <?= BUSINESS_NAME ?></title><?php include '../../ui/layout/head.php'; ?></head>
<body>
<?php include '../../ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../ui/layout/topbar.php'; ?>
    <div class="page-body">
        <div class="section-header" style="margin-bottom:8px">
            <div><h1 class="page-title">Usuarios</h1><p class="page-subtitle">Gestión de accesos al sistema</p></div>
            <button class="btn-primary" onclick="document.getElementById('mNuevo').classList.add('show')">+ Nuevo usuario</button>
        </div>
        <?php if($msg): ?><div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div><?php endif; ?>

        <!-- Tabla de permisos -->
        <div class="section-card" style="margin-bottom:16px">
            <div class="section-header"><h2>Permisos por rol</h2></div>
            <table class="data-table">
                <thead><tr><th>Rol</th><th>POS/Ventas</th><th>Clientes</th><th>Citas</th><th>Reportes</th><th>Configuración</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-red">Admin</span></td><td>✅</td><td>✅</td><td>✅</td><td>✅</td><td>✅</td></tr>
                    <tr><td><span class="badge badge-blue">Cajero</span></td><td>✅</td><td>✅</td><td>✅</td><td>✅</td><td>❌</td></tr>
                    <tr><td><span class="badge badge-green">Recepcionista</span></td><td>✅</td><td>✅</td><td>✅</td><td>❌</td><td>❌</td></tr>
                    <tr><td><span class="badge badge-gray">Solo lectura</span></td><td>❌</td><td>❌</td><td>❌</td><td>✅</td><td>❌</td></tr>
                </tbody>
            </table>
        </div>

        <div class="section-card">
            <table class="data-table">
                <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php while($u=$users->fetch_assoc()):$ri=$roles[$u['role']]??['?','badge-gray']; ?>
                    <tr style="<?= !$u['is_active']?'opacity:0.5':'' ?>">
                        <td><strong style="color:var(--text)"><?= htmlspecialchars($u['name']) ?></strong><?php if($u['id']==$_SESSION['user_id']): ?> <span style="font-size:11px;color:var(--text-dim)">(tú)</span><?php endif; ?></td>
                        <td style="color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge <?= $ri[1] ?>"><?= $ri[0] ?></span></td>
                        <td><span class="badge <?= $u['is_active']?'badge-green':'badge-gray' ?>"><?= $u['is_active']?'Activo':'Inactivo' ?></span></td>
                        <td><div style="display:flex;gap:6px">
                            <?php if($u['id']!==1): ?>
                            <button class="btn-secondary btn-sm" onclick="openRol(<?= $u['id'] ?>,'<?= $u['role'] ?>')">Rol</button>
                            <button class="btn-secondary btn-sm" onclick="openPass(<?= $u['id'] ?>,'<?= htmlspecialchars($u['name']) ?>')">Contraseña</button>
                            <?php if($u['id']!=$_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('¿Confirmar?')">
                                <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="status" value="<?= $u['is_active']?0:1 ?>">
                                <button type="submit" class="<?= $u['is_active']?'btn-danger':'btn-primary' ?> btn-sm"><?= $u['is_active']?'Desactivar':'Activar' ?></button>
                            </form>
                            <?php endif; ?><?php endif; ?>
                        </div></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="mNuevo">
    <div class="modal"><div class="modal-title">Nuevo usuario <button class="modal-close" onclick="document.getElementById('mNuevo').classList.remove('show')">✕</button></div>
    <form method="POST"><input type="hidden" name="action" value="create">
        <div class="form-group"><label>Nombre *</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Contraseña *</label><input type="password" name="password" placeholder="Mínimo 6 caracteres" required></div>
        <div class="form-group"><label>Rol *</label><select name="role"><option value="cashier">Cajero</option><option value="receptionist">Recepcionista</option><option value="viewer">Solo lectura</option><option value="admin">Admin</option></select></div>
        <div class="modal-actions"><button type="button" class="btn-secondary" onclick="document.getElementById('mNuevo').classList.remove('show')">Cancelar</button><button type="submit" class="btn-primary">Crear</button></div>
    </form></div>
</div>

<div class="modal-overlay" id="mRol">
    <div class="modal"><div class="modal-title">Cambiar rol <button class="modal-close" onclick="document.getElementById('mRol').classList.remove('show')">✕</button></div>
    <form method="POST"><input type="hidden" name="action" value="role"><input type="hidden" name="id" id="rId">
        <div class="form-group"><label>Rol</label><select name="role" id="rSel"><option value="cashier">Cajero</option><option value="receptionist">Recepcionista</option><option value="viewer">Solo lectura</option><option value="admin">Admin</option></select></div>
        <div class="modal-actions"><button type="button" class="btn-secondary" onclick="document.getElementById('mRol').classList.remove('show')">Cancelar</button><button type="submit" class="btn-primary">Guardar</button></div>
    </form></div>
</div>

<div class="modal-overlay" id="mPass">
    <div class="modal"><div class="modal-title">Cambiar contraseña <button class="modal-close" onclick="document.getElementById('mPass').classList.remove('show')">✕</button></div>
    <p id="pInfo" style="font-size:13px;color:var(--text-muted);margin-bottom:14px"></p>
    <form method="POST"><input type="hidden" name="action" value="reset"><input type="hidden" name="id" id="pId">
        <div class="form-group"><label>Nueva contraseña</label><input type="password" name="new_password" placeholder="Mínimo 6 caracteres" required></div>
        <div class="modal-actions"><button type="button" class="btn-secondary" onclick="document.getElementById('mPass').classList.remove('show')">Cancelar</button><button type="submit" class="btn-primary">Actualizar</button></div>
    </form></div>
</div>

<script>
function openRol(id,role){document.getElementById('rId').value=id;document.getElementById('rSel').value=role;document.getElementById('mRol').classList.add('show');}
function openPass(id,name){document.getElementById('pId').value=id;document.getElementById('pInfo').textContent='Usuario: '+name;document.getElementById('mPass').classList.add('show');}
</script>
</body>
</html>