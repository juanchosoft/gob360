<?php
require './admin/include/generic_classes.php';
include './admin/include/head.php';
?>

<body>

<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>

<style>
body{
    background: linear-gradient(135deg,#0b1221,#0c2327,#0a1121);
    color:#fff;
}

/* TITULOS */
.titulo{
    font-size:28px;
    font-weight:900;
    text-align:center;
    margin:20px 0;
}

/* CARDS PRINCIPALES */
.card-box{
    background: rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.12);
    border-radius:16px;
    padding:15px;
    margin-bottom:15px;
}

.card-box h4{
    text-align:center;
    font-weight:bold;
    color:#4fc3f7;
}

.valor{
    color:#ff5252;
    font-size:18px;
    font-weight:bold;
    text-align:center;
}

/* GESTION SEGURIDAD */
.seguridad{
    background: linear-gradient(135deg,#20427F,#132b52);
    padding:20px;
    border-radius:20px;
}

.seguridad img{
    width:100%;
    height:160px;
    object-fit:cover;
    border-radius:12px;
    margin-bottom:10px;
}

.seguridad-card{
    background:#4f7db3;
    border-radius:20px;
    padding:20px;
    text-align:center;
    height:100%;
}

.seguridad-card h3{
    font-size:40px;
}

</style>

<div class="pcoded-main-container">
<div class="pcoded-content">

<div class="titulo">🛡️ DASHBOARD SEGURIDAD</div>

<!-- =========================
   BLOQUES SUPERIORES
========================= -->
<div class="row">

<div class="col-md-4">
<div class="card-box">
<h4>MOVILIDAD</h4>
<p>49 motocicletas</p>
<p>14 camionetas</p>
<div class="valor">17.545.275.126</div>
</div>
</div>

<div class="col-md-4">
<div class="card-box">
<h4>TECNOLOGÍA</h4>
<p>Biometría, Software, Cámaras</p>
<div class="valor">7.423.762.240</div>
</div>
</div>

<div class="col-md-4">
<div class="card-box">
<h4>PROYECTOS</h4>
<p>CIMVI, SIART, CEGES</p>
<div class="valor">24.700.000.000</div>
</div>
</div>

</div>

<div class="row">

<div class="col-md-4">
<div class="card-box">
<h4>INTENDENCIA</h4>
<p>Chalecos, raciones, combustible</p>
<div class="valor">2.134.297.744</div>
</div>
</div>

<div class="col-md-4">
<div class="card-box">
<h4>INFRAESTRUCTURA</h4>
<p>Estaciones, CAE, mantenimiento</p>
<div class="valor">3.298.301.574</div>
</div>
</div>

<div class="col-md-4">
<div class="card-box">
<h4>CONVENIOS</h4>
<p>Comunidades y convivencia</p>
<div class="valor">7.876.692.640</div>
</div>
</div>

</div>

<!-- =========================
   GESTION SEGURIDAD
========================= -->

<div class="titulo">📊 Gestión en Seguridad</div>

<div class="seguridad">

<div class="row">

<div class="col-md-2">
<img src="assets/img/admin/estadistica3.png">
<div class="seguridad-card">
<h5>Consejos</h5>
<h3>29</h3>
<p>Ministeriales 04</p>
</div>
</div>

<div class="col-md-2">
<img src="assets/img/admin/estadistica3.png">
<div class="seguridad-card">
<h5>PMU</h5>
<h3>59</h3>
<p>Seguimiento social</p>
</div>
</div>

<div class="col-md-2">
<img src="assets/img/admin/estadistica3.png">
<div class="seguridad-card">
<h5>Reuniones</h5>
<h3>57</h3>
<p>Fuerza pública</p>
</div>
</div>

<div class="col-md-2">
<img src="assets/img/admin/estadistica3.png">
<div class="seguridad-card">
<h5>Comités</h5>
<h3>15</h3>
<p>Orden público</p>
</div>
</div>

<div class="col-md-2">
<img src="assets/img/admin/estadistica3.png">
<div class="seguridad-card">
<h5>Mesas</h5>
<h3>47</h3>
<p>Trabajo técnico</p>
</div>
</div>

</div>

</div>

</div>
</div>

</body>
</html>