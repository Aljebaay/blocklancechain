<?php
@session_start();
if(!isset($_SESSION['admin_email'])){
  echo "<script>window.open('login','_self');</script>";
  exit();
}
?>

<div class="breadcrumbs">
  <div class="col-sm-6">
    <div class="page-header float-left">
      <div class="page-title">
        <h1><i class="menu-icon fa fa-cog"></i> Settings / Open Source</h1>
      </div>
    </div>
  </div>
</div>

<div class="container pt-3">
  <div class="row">
    <div class="col-lg-12">
      <div class="card mb-5">
        <div class="card-header">
          <h4 class="h4 mb-0"><i class="fa fa-unlock fa-fw"></i> License Check Removed</h4>
        </div>
        <div class="card-body">
          <p class="mb-0">This installation runs in open-source mode. Purchase code verification is disabled.</p>
        </div>
      </div>
    </div>
  </div>
</div>

