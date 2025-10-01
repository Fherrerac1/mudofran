<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista de Pruebas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn {
            padding: 15px 40px;
            font-size: 18px;
            background-color: #fff;
            color: #667eea;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: #333;
            font-size: 24px;
        }

        .modal-body {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.6;
        }

        .modal-body label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: bold;
        }

        .formato-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            color: #333;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
            margin-bottom: 20px;
        }

        .formato-select:hover {
            border-color: #667eea;
        }

        .formato-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .file-upload {
            margin-top: 15px;
        }

        .file-input {
            width: 100%;
            padding: 10px;
            border: 2px dashed #e0e0e0;
            border-radius: 6px;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .file-input:hover {
            border-color: #667eea;
        }

        .modal-footer {
            text-align: right;
        }

        .close-btn {
            padding: 10px 25px;
            background-color: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .close-btn:hover {
            background-color: #764ba2;
        }

        .cancel-btn {
            background-color: #e0e0e0;
            color: #333;
            margin-right: 10px;
        }

        .cancel-btn:hover {
            background-color: #d0d0d0;
        }

        .import-btn {
            background-color: #667eea;
        }

        .import-btn:hover {
            background-color: #764ba2;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <button class="btn" onclick="abrirVentana()">Abrir Ventana</button>

    <div id="miModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Importación de archivos</h2>
            </div>
            <div class="modal-body">
                <label for="formato">Selecciona el formato:</label>
                <select id="formato" name="formato" class="formato-select">
                    <option value="">-- Selecciona un formato --</option>
                    <option value="excel">Excel (.xlsx, .xls)</option>
                    <option value="csv">CSV (.csv)</option>
                    <option value="txt">Texto (.txt)</option>
                    <option value="json">JSON (.json)</option>
                </select>
                
                <div class="file-upload">
                    <input type="file" id="archivo" name="archivo" class="file-input">
                </div>
            </div>
            <div class="modal-footer">
                <button class="close-btn cancel-btn" onclick="cerrarVentana()">Cancelar</button>
                <button class="close-btn import-btn" onclick="importarArchivo()">Importar</button>
            </div>
        </div>
    </div>

    <script>
        function abrirVentana() {
            document.getElementById('miModal').classList.add('active');
        }

        function cerrarVentana() {
            document.getElementById('miModal').classList.remove('active');
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('miModal');
            if (event.target == modal) {
                cerrarVentana();
            }
        }

        function importarArchivo() {
            const formato = document.getElementById('formato').value;
            const archivo = document.getElementById('archivo').files[0];

            if (!formato) {
                alert('Por favor, selecciona un formato');
                return;
            }

            if (!archivo) {
                alert('Por favor, selecciona un archivo');
                return;
            }

            console.log('Formato seleccionado:', formato);
            console.log('Archivo seleccionado:', archivo.name);
            
            // Aquí puedes agregar el código para enviar el archivo al servidor
            alert('Archivo listo para importar: ' + archivo.name);
            cerrarVentana();
        }
    </script>
</body>
</html>