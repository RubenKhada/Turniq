<?php
require_once '../../config/app.php';
require_once '../../config/database.php';
requireLogin();
requirePermission('pos');

$db = getDB();
$products_raw = $db->query("SELECT id,name,category,price,stock,sku,auto_barcode,barcode FROM products WHERE is_active=1 AND stock>0 ORDER BY category,name");
$services_raw = $db->query("SELECT id,name,price,duration_minutes FROM services WHERE is_active=1 ORDER BY name");

$products_json = [];
$praw2 = $db->query("SELECT id,name,category,price,stock,sku,auto_barcode,barcode FROM products WHERE is_active=1 AND stock>0");
while($p = $praw2->fetch_assoc()) $products_json[] = $p;
$db->close();
$extra_css = 'pos.css';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>POS — <?= BUSINESS_NAME ?></title>
    <?php include '../../ui/layout/head.php'; ?>
    <style>
        .pos-layout{display:grid;grid-template-columns:1fr 360px;gap:16px;height:calc(100vh - 130px)}
        .pos-products{display:flex;flex-direction:column;gap:10px;overflow:hidden}
        .pos-search input{width:100%;padding:11px 14px;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text);font-size:14px;outline:none}
        .pos-search input:focus{border-color:var(--primary)}
        .pos-tabs{display:flex;gap:4px;background:var(--bg);padding:4px;border-radius:var(--radius-md);border:1px solid var(--border)}
        .pos-tab{flex:1;padding:8px;border:none;border-radius:6px;background:transparent;color:var(--text-muted);font-size:13px;font-weight:500;cursor:pointer;transition:all 0.15s;font-family:inherit}
        .pos-tab.active{background:var(--bg-card);color:var(--text);box-shadow:var(--shadow)}
        .products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;overflow-y:auto}
        .product-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px;cursor:pointer;transition:all 0.15s;user-select:none}
        .product-card:hover{border-color:var(--primary);background:var(--bg-hover);transform:translateY(-1px)}
        .product-card:active{transform:scale(0.97)}
        .p-category{font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px}
        .p-name{font-size:12px;color:var(--text);font-weight:500;margin-bottom:8px;line-height:1.3}
        .p-price{font-size:16px;font-weight:600;color:var(--accent)}
        .p-stock{font-size:11px;color:var(--text-dim);margin-top:3px}
        .service-card{background:var(--primary-light);border-color:var(--primary)}
        .pos-cart{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);display:flex;flex-direction:column;overflow:hidden}
        .cart-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg-subtle)}
        .cart-header h2{font-size:14px;font-weight:600;color:var(--text)}
        .cart-items{flex:1;overflow-y:auto;padding:8px}
        .cart-empty{text-align:center;color:var(--text-dim);padding:32px 16px;font-size:13px}
        .cart-item{display:flex;align-items:center;gap:8px;padding:9px 8px;border-bottom:1px solid var(--border-light)}
        .cart-item:last-child{border-bottom:none}
        .cart-item-info{flex:1;min-width:0}
        .cart-item-name{font-size:12px;color:var(--text);font-weight:500}
        .cart-item-price{font-size:11px;color:var(--text-muted);margin-top:2px}
        .cart-item-controls{display:flex;align-items:center;gap:4px}
        .qty-btn{width:24px;height:24px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.15s;font-family:inherit}
        .qty-btn:hover{background:var(--primary);color:#fff;border-color:var(--primary)}
        .qty-num{width:26px;text-align:center;font-size:13px;color:var(--text);font-weight:600}
        .remove-btn{background:none;border:none;color:var(--text-dim);cursor:pointer;font-size:14px;padding:3px 5px;border-radius:4px;transition:all 0.15s}
        .remove-btn:hover{color:var(--danger);background:var(--danger-bg)}
        .cart-footer{border-top:1px solid var(--border);padding:14px 16px;background:var(--bg-subtle)}
        .total-row{display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-bottom:5px}
        .total-row.main{font-size:20px;font-weight:600;color:var(--text);margin-top:8px;padding-top:10px;border-top:2px solid var(--border)}
        .payment-methods{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:10px 0}
        .pay-btn{padding:8px 4px;border-radius:var(--radius-md);border:1px solid var(--border);background:var(--bg-card);color:var(--text-muted);font-size:11px;font-weight:500;cursor:pointer;transition:all 0.15s;text-align:center;font-family:inherit}
        .pay-btn:hover{border-color:var(--primary);color:var(--accent)}
        .pay-btn.selected{background:var(--primary-light);border-color:var(--primary);color:var(--accent);font-weight:600}
        .btn-cobrar{width:100%;padding:13px;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-md);font-size:15px;font-weight:600;cursor:pointer;transition:background 0.2s;font-family:inherit}
        .btn-cobrar:hover{background:var(--primary-h)}
        .btn-cobrar:disabled{background:var(--border);color:var(--text-dim);cursor:not-allowed}
        .custom-service{background:var(--primary-light);border:1px dashed var(--primary);border-radius:var(--radius-md);padding:14px}
        .custom-service-title{font-size:13px;font-weight:600;color:var(--accent);margin-bottom:10px}
    </style>
</head>
<body>
<?php include '../../ui/layout/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../ui/layout/topbar.php'; ?>
    <div class="page-body" style="padding-bottom:0">
        <div class="section-header" style="margin-bottom:12px">
            <div>
                <h1 class="page-title">Punto de Venta</h1>
                <p class="page-subtitle">Escanea, busca o selecciona productos y servicios</p>
            </div>
        </div>
        <div class="pos-layout">
            <div class="pos-products">
                <div class="pos-search" style="position:relative">
                    <input type="text" id="searchInput" placeholder="🔍  Buscar o escanear código de barras..." oninput="filterItems()" onkeydown="handleKeydown(event)" autocomplete="off">
                    <div id="scanIndicator" style="display:none;position:absolute;right:12px;top:50%;transform:translateY(-50%);background:var(--success-bg);color:var(--success);font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px"></div>
                </div>
                <div class="pos-tabs">
                    <button class="pos-tab active" onclick="showTab('productos',this)">📦 Productos</button>
                    <button class="pos-tab" onclick="showTab('servicios',this)">🔧 Servicios</button>
                    <button class="pos-tab" onclick="showTab('personalizado',this)">✏️ Personalizado</button>
                </div>
                <div id="tab-productos" class="products-grid">
                    <?php $products_raw->data_seek(0); while($p = $products_raw->fetch_assoc()): ?>
                    <div class="product-card"
                         data-id="prod_<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>"
                         data-price="<?= $p['price'] ?>" data-stock="<?= $p['stock'] ?>" data-type="product"
                         data-search="<?= strtolower(htmlspecialchars($p['name'].' '.$p['category'].' '.$p['sku'].' '.$p['barcode'].' '.$p['auto_barcode'])) ?>"
                         onclick="addToCart(this)">
                        <div class="p-category"><?= htmlspecialchars($p['category'] ?? '') ?></div>
                        <div class="p-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="p-price"><?= formatMoney($p['price']) ?></div>
                        <div class="p-stock">Stock: <?= $p['stock'] ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <div id="tab-servicios" class="products-grid" style="display:none">
                    <?php $services_raw->data_seek(0); while($s = $services_raw->fetch_assoc()): ?>
                    <div class="product-card service-card"
                         data-id="serv_<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['name']) ?>"
                         data-price="<?= $s['price'] ?>" data-stock="999" data-type="service"
                         data-search="<?= strtolower(htmlspecialchars($s['name'])) ?>"
                         onclick="addToCart(this)">
                        <div class="p-category">Servicio · <?= $s['duration_minutes'] ?> min</div>
                        <div class="p-name"><?= htmlspecialchars($s['name']) ?></div>
                        <div class="p-price"><?= formatMoney($s['price']) ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <div id="tab-personalizado" style="display:none">
                    <div class="custom-service">
                        <div class="custom-service-title">✏️ Servicio personalizado</div>
                        <div class="form-group"><label>Descripción</label><input type="text" id="customName" placeholder="Ej: Corte especial..."></div>
                        <div style="display:grid;grid-template-columns:1fr auto;gap:8px;align-items:end">
                            <div class="form-group" style="margin:0"><label>Precio</label><input type="number" id="customPrice" step="0.01" min="0.01" placeholder="0.00"></div>
                            <button class="btn-primary" onclick="addCustomService()" style="height:38px">+ Agregar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pos-cart">
                <div class="cart-header"><h2>🛒 Carrito</h2><button onclick="clearCart()" class="btn-secondary btn-sm">Limpiar</button></div>
                <div class="cart-items" id="cartItems"><div class="cart-empty" id="cartEmpty"><div>🛒</div><p style="margin-top:8px">Sin productos</p><small>Toca o escanea un producto</small></div></div>
                <div class="cart-footer">
                    <div class="cart-totals">
                        <div class="total-row"><span>Subtotal</span><span id="subtotalDisplay">$0.00</span></div>
                        <div class="total-row main"><span>Total</span><span id="totalDisplay">$0.00</span></div>
                    </div>
                    <div style="margin-top:10px"><input type="text" id="clientName" placeholder="Cliente (opcional)" style="width:100%;padding:8px 12px;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text);font-size:13px;outline:none;font-family:inherit"></div>
                    <div class="payment-methods">
                        <button class="pay-btn selected" onclick="selectPayment('cash',this)">💵 Efectivo</button>
                        <button class="pay-btn" onclick="selectPayment('card',this)">💳 Tarjeta</button>
                        <button class="pay-btn" onclick="selectPayment('transfer',this)">🏦 Transfer.</button>
                    </div>
                    <button class="btn-cobrar" id="btnCobrar" onclick="procesarVenta()" disabled>Cobrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-overlay" id="modalRecibo">
    <div class="modal">
        <div class="modal-title"><span>✅ Venta registrada</span></div>
        <div id="reciboContent"></div>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="imprimirRecibo()">🖨️ Imprimir</button>
            <button class="btn-primary" onclick="nuevaVenta()">Nueva venta</button>
        </div>
    </div>
</div>
<script>
const PRODUCTS_DB = <?= json_encode($products_json) ?>;
let cart={}, paymentMethod='cash', lastSaleId=null, lastKeyTime=0, barcodeTimer=null;

function handleKeydown(e) {
    const now=Date.now(), diff=now-lastKeyTime; lastKeyTime=now;
    if(e.key==='Enter'){const code=document.getElementById('searchInput').value.trim();if(code){const f=findByBarcode(code);if(f){addToCartById(f);document.getElementById('searchInput').value='';filterItems();showScanFeedback(true,f.name);}else showScanFeedback(false);}return;}
    if(diff<80){clearTimeout(barcodeTimer);barcodeTimer=setTimeout(()=>{const code=document.getElementById('searchInput').value.trim();if(code.length>=6){const f=findByBarcode(code);if(f){addToCartById(f);document.getElementById('searchInput').value='';filterItems();showScanFeedback(true,f.name);}}},150);}
}
function findByBarcode(code){const c=code.trim().toLowerCase();return PRODUCTS_DB.find(p=>(p.barcode&&p.barcode.toLowerCase()===c)||(p.auto_barcode&&p.auto_barcode.toLowerCase()===c)||(p.sku&&p.sku.toLowerCase()===c))||null;}
function addToCartById(p){const id='prod_'+p.id;if(cart[id]){if(cart[id].qty>=parseInt(p.stock)){alert('Sin stock.');return;}cart[id].qty++;}else cart[id]={id,name:p.name,price:parseFloat(p.price),stock:parseInt(p.stock),qty:1,type:'product'};renderCart();}
function showScanFeedback(ok,name=''){const el=document.getElementById('scanIndicator'),inp=document.getElementById('searchInput');el.style.display='block';el.style.background=ok?'var(--success-bg)':'var(--danger-bg)';el.style.color=ok?'var(--success)':'var(--danger)';el.textContent=ok?'✅ '+name:'❌ No encontrado';inp.style.borderColor=ok?'var(--success)':'var(--danger)';setTimeout(()=>{el.style.display='none';inp.style.borderColor='';},2000);}
function showTab(tab,btn){['productos','servicios','personalizado'].forEach(t=>{const el=document.getElementById('tab-'+t);if(el)el.style.display='none';});document.querySelectorAll('.pos-tab').forEach(b=>b.classList.remove('active'));const t=document.getElementById('tab-'+tab);if(t)t.style.display=tab==='personalizado'?'block':'grid';btn.classList.add('active');}
function addToCart(el){const id=el.dataset.id,name=el.dataset.name,price=parseFloat(el.dataset.price),stock=parseInt(el.dataset.stock),type=el.dataset.type;if(cart[id]){if(cart[id].qty>=stock){alert('Sin stock.');return;}cart[id].qty++;}else cart[id]={id,name,price,stock,qty:1,type};renderCart();}
function addCustomService(){const name=document.getElementById('customName').value.trim(),price=parseFloat(document.getElementById('customPrice').value);if(!name||!price||price<=0){alert('Escribe nombre y precio.');return;}const id='custom_'+Date.now();cart[id]={id,name,price,stock:999,qty:1,type:'custom'};document.getElementById('customName').value='';document.getElementById('customPrice').value='';renderCart();}
function renderCart(){const container=document.getElementById('cartItems'),keys=Object.keys(cart);if(keys.length===0){container.innerHTML='<div class="cart-empty" id="cartEmpty"><div>🛒</div><p style="margin-top:8px">Sin productos</p><small>Toca o escanea un producto</small></div>';document.getElementById('btnCobrar').disabled=true;updateTotals();return;}document.getElementById('btnCobrar').disabled=false;let html='';keys.forEach(id=>{const item=cart[id],icon=item.type==='product'?'📦':item.type==='service'?'🔧':'✏️';html+=`<div class="cart-item"><div class="cart-item-info"><div class="cart-item-name">${icon} ${item.name}</div><div class="cart-item-price">${formatM(item.price)} c/u · ${formatM(item.price*item.qty)}</div></div><div class="cart-item-controls"><button class="qty-btn" onclick="changeQty('${id}',-1)">−</button><span class="qty-num">${item.qty}</span><button class="qty-btn" onclick="changeQty('${id}',1)">+</button><button class="remove-btn" onclick="removeItem('${id}')">✕</button></div></div>`;});container.innerHTML='<div class="cart-empty" id="cartEmpty" style="display:none"></div>'+html;updateTotals();}
function formatM(n){return '$'+n.toFixed(2);}
function changeQty(id,d){cart[id].qty+=d;if(cart[id].qty<=0)delete cart[id];else if(cart[id].qty>cart[id].stock)cart[id].qty=cart[id].stock;renderCart();}
function removeItem(id){delete cart[id];renderCart();}
function clearCart(){cart={};renderCart();}
function updateTotals(){let t=0;Object.values(cart).forEach(i=>t+=i.price*i.qty);document.getElementById('subtotalDisplay').textContent=formatM(t);document.getElementById('totalDisplay').textContent=formatM(t);}
function selectPayment(m,btn){paymentMethod=m;document.querySelectorAll('.pay-btn').forEach(b=>b.classList.remove('selected'));btn.classList.add('selected');}
function filterItems(){const q=document.getElementById('searchInput').value.toLowerCase();document.querySelectorAll('.product-card').forEach(c=>c.style.display=c.dataset.search.includes(q)?'':'none');}
async function procesarVenta(){if(!Object.keys(cart).length)return;const items=Object.values(cart),total=items.reduce((s,i)=>s+i.price*i.qty,0),clientName=document.getElementById('clientName').value||'Público general';document.getElementById('btnCobrar').disabled=true;document.getElementById('btnCobrar').textContent='Procesando...';try{const r=await fetch('save_sale.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({items,total,paymentMethod,clientName})});const res=await r.json();if(res.success){lastSaleId=res.sale_id;mostrarRecibo(res.sale_id,items,total,clientName,paymentMethod);}else{alert('Error: '+res.message);document.getElementById('btnCobrar').disabled=false;document.getElementById('btnCobrar').textContent='Cobrar';}}catch(e){alert('Error de conexión.');document.getElementById('btnCobrar').disabled=false;document.getElementById('btnCobrar').textContent='Cobrar';}}
function mostrarRecibo(id,items,total,cliente,method){const mn={cash:'Efectivo',card:'Tarjeta',transfer:'Transferencia'},now=new Date();const iH=items.map(i=>`<tr><td style="padding:5px 0;font-size:13px;color:var(--text)">${i.name}</td><td style="text-align:center;color:var(--text-muted);font-size:13px">${i.qty}</td><td style="text-align:right;color:var(--accent);font-size:13px;font-weight:600">${formatM(i.price*i.qty)}</td></tr>`).join('');document.getElementById('reciboContent').innerHTML=`<div style="text-align:center;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border)"><div style="font-size:13px;color:var(--text-muted)">${now.toLocaleDateString('es-MX')} ${now.toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'})}</div><div style="font-size:12px;color:var(--text-dim)">Venta #${String(id).padStart(4,'0')}</div><div style="font-size:13px;margin-top:4px">${cliente}</div></div><table style="width:100%;border-collapse:collapse"><thead><tr style="border-bottom:1px solid var(--border)"><th style="text-align:left;font-size:11px;color:var(--text-muted);padding-bottom:6px">Producto</th><th style="text-align:center;font-size:11px;color:var(--text-muted)">Qty</th><th style="text-align:right;font-size:11px;color:var(--text-muted)">Total</th></tr></thead><tbody>${iH}</tbody></table><div style="margin-top:12px;padding-top:12px;border-top:2px solid var(--border);display:flex;justify-content:space-between;align-items:center"><span style="color:var(--text-muted);font-size:13px">${mn[method]}</span><span style="font-size:22px;font-weight:700">${formatM(total)}</span></div>`;document.getElementById('modalRecibo').classList.add('show');}
function imprimirRecibo(){window.open(`print_receipt.php?id=${lastSaleId}`,'_blank','width=420,height=650');}
function nuevaVenta(){cart={};paymentMethod='cash';renderCart();document.getElementById('clientName').value='';document.getElementById('modalRecibo').classList.remove('show');document.getElementById('btnCobrar').textContent='Cobrar';document.getElementById('btnCobrar').disabled=true;location.reload();}
window.onload=()=>document.getElementById('searchInput').focus();
</script>
</body>
</html>