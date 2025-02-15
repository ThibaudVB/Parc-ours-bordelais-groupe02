<?php
include '../../../header.php';
include __DIR__ . '/../../../perm/permission_admin.php';

if (isset($_GET['numStat'])) {
    $numStat = $_GET['numStat'];
    $libStat = sql_select("STATUT", "libStat", "numStat = $numStat")[0]['libStat'];
    $fk = sql_select("membre", "numStat", "numStat = $numStat");
}
?>



<!-- Bootstrap form to create a new statut -->
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Suppression Statut</h1>
        </div>
        <div class="col-md-12">
            <!-- Form to create a new statut -->
            <form action="<?php echo ROOT_URL . '/api/statuts/delete.php' ?>" method="post">
                <div class="form-group">
                    <label for="libStat">Nom du statut</label>
                    <input id="numStat" name="numStat" class="form-control" style="display: none" type="text"
                        value="<?php echo ($numStat); ?>" readonly="readonly" />
                    <input id="libStat" name="libStat" class="form-control" type="text"
                        value="<?php echo ($libStat); ?>" readonly="readonly" disabled />
                </div>
                <br />
                <div class="form-group mt-2">
                    <?php
                    if ($sql = "SELECT * FROM table WHERE fk IS NULL") {
                        $desactiver = true;
                    }
                    ?>

                    <a href="list.php" class="btn btn-primary">List</a>
                    <?php 
                    if (count($fk)!=0){
                        echo '<button type="submit" class="btn btn-danger" disabled> Annulation impossible</button>';
                    }
                    else {
                        echo '<button type="submit" class="btn btn-danger">Confirmer delete ? </button>';
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>
</div>