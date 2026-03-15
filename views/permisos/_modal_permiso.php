<!-- Modal Permiso (partial compartido entre index y detalle) -->
<div class="modal fade" id="modalPermiso" tabindex="-1" role="dialog" aria-labelledby="modalPermisoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning" id="modalPermisoHeader">
                <h5 class="modal-title" id="modalPermisoLabel">Editar Permiso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPermiso" method="post">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                    <input type="hidden" id="permisoAction" name="action" value="edit">
                    <input type="hidden" id="idPermiso" name="idpermiso" value="">

                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <small class="form-text text-muted">Nombre único para el permiso</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnGuardarPermiso">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>