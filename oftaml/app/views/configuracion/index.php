<div class="container-fluid px-4">
    <h1 class="mt-4">Ajustes del Sistema</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Información General</li>
    </ol>

    <form id="formConfiguracion" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-info-circle me-1"></i>Información de la Clínica</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nombre_clinica" class="form-label">Nombre de la Clínica</label>
                            <input type="text" class="form-control" id="nombre_clinica" name="nombre_clinica" required>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="telefono" class="form-label">Teléfono</label><input type="text" class="form-control" id="telefono" name="telefono"></div>
                            <div class="col-md-6 mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email"></div>
                        </div>
                        <div class="mb-3">
                            <label for="rtn" class="form-label">RTN</label>
                            <input type="text" class="form-control" id="rtn" name="rtn">
                        </div>
                        
                        <div class="mb-3">
                            <label for="zona_horaria" class="form-label"><i class="fas fa-clock me-1"></i>Zona Horaria del Sistema</label>
                            <select class="form-select" id="zona_horaria" name="zona_horaria" required>
                                <option value="America/Tegucigalpa">Honduras / Centroamérica (UTC-6)</option>
                                <option value="America/Mexico_City">México (Centro)</option>
                                <option value="America/Bogota">Colombia / Panamá / Perú</option>
                                <option value="America/Caracas">Venezuela</option>
                                <option value="America/La_Paz">Bolivia</option>
                                <option value="America/Santiago">Chile</option>
                                <option value="America/Argentina/Buenos_Aires">Argentina / Uruguay</option>
                                <option value="Europe/Madrid">España</option>
                                <option value="America/New_York">Estados Unidos (Este)</option>
                                <option value="America/Los_Angeles">Estados Unidos (Oeste)</option>
                            </select>
                            <small class="text-muted">Esto ajustará la hora de registros, citas y facturas.</small>
                        </div>
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo de la Clínica</label>
                            <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
                            <small class="form-text text-muted">Dejar en blanco para no cambiar el logo actual.</small>
                            <div id="logo_preview_container" class="mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-palette me-1"></i>Personalización de Apariencia</div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Modo del Tema</label>
                            <div>
                                <input type="radio" class="btn-check" name="theme_mode" id="theme_light" value="light" autocomplete="off">
                                <label class="btn btn-outline-primary" for="theme_light"><i class="fas fa-sun me-1"></i>Modo Claro</label>
                                <input type="radio" class="btn-check" name="theme_mode" id="theme_dark" value="dark" autocomplete="off">
                                <label class="btn btn-outline-dark" for="theme_dark"><i class="fas fa-moon me-1"></i>Modo Oscuro</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="background_color" class="form-label">Color de Fondo (Solo en Modo Claro)</label>
                            <input type="color" class="form-control form-control-color w-100" id="background_color" name="background_color" title="Elige un color">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label for="navbar_color" class="form-label">Color de la Barra de Navegación</label>
                            <input type="color" class="form-control form-control-color w-100" id="navbar_color" name="navbar_color" title="Elige un color">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="navbar_sticky" name="navbar_sticky" value="1">
                            <label class="form-check-label" for="navbar_sticky">Dejar barra de navegación fija al tope</label>
                        </div>

                        <div class="card mb-4 mt-3">
                            <div class="card-header"><i class="fas fa-sign-in-alt me-1"></i>Personalizar Login</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="login_background_color" class="form-label">Color de fondo del Login</label>
                                    <input type="color" class="form-control form-control-color w-100" id="login_background_color" name="login_background_color" title="Elige un color para la pantalla de login">
                                </div>

                                <div class="mb-3">
                                    <label for="login_background_image" class="form-label">Imagen de fondo del Login (opcional)</label>
                                    <input class="form-control" type="file" id="login_background_image" name="login_background_image" accept="image/*">
                                    <div class="mt-2">
                                        <img id="login_bg_preview" src="" alt="Preview imagen login" style="max-width:100%; max-height:160px; display:none; border-radius:6px; border:1px solid #ddd;">
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="login_bg_remove" name="login_bg_remove">
                                        <label class="form-check-label" for="login_bg_remove">Eliminar imagen actual</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
            </div>
        </div>
    </form>

    <div class="card mb-5 border-warning">
        <div class="card-header bg-warning text-dark">
            <i class="fas fa-database me-1"></i> Mantenimiento de Base de Datos
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5><i class="fas fa-download me-2"></i>Crear Respaldo (Backup)</h5>
                    <p class="text-muted small">Descarga una copia completa de la base de datos (estructura y datos) en formato SQL. Se recomienda hacer esto semanalmente.</p>
                    <button id="btnGenerarBackup" class="btn btn-outline-dark w-100">
                        <i class="fas fa-file-download me-2"></i>Descargar Respaldo SQL
                    </button>
                </div>
                <div class="col-md-6 border-start">
                    <h5 class="text-danger"><i class="fas fa-upload me-2"></i>Restaurar Base de Datos</h5>
                    <p class="text-muted small">Carga un archivo SQL para restaurar el sistema. <strong>¡CUIDADO!</strong> Esto borrará los datos actuales y los reemplazará con los del archivo.</p>
                    
                    <form id="formRestaurarDB" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="file" class="form-control" id="archivo_sql" name="archivo_sql" accept=".sql" required>
                            <button class="btn btn-danger" type="submit" id="btnRestaurar">Restaurar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

<script>
(function(){
    function readURL(input){
        if(input.files && input.files[0]){
            var reader = new FileReader();
            reader.onload = function(e){
                var img = document.getElementById('login_bg_preview');
                img.src = e.target.result;
                img.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    var fileInput = document.getElementById('login_background_image');
    if(fileInput){
        fileInput.addEventListener('change', function(){
            readURL(this);
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        var img = document.getElementById('login_bg_preview');
        if(img && img.getAttribute('data-src')){
            img.src = img.getAttribute('data-src');
            img.style.display = 'block';
        }
    });
})();
</script>