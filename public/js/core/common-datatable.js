/**
 * common-datatable.js - Utilidades comunes para DataTables
 * 
 * Este archivo proporciona funciones de ayuda para la configuración de DataTables
 * sin interferir con las configuraciones existentes.
 * 
 * @package ProyectoBase
 * @subpackage JavaScript\Core
 * @author Jandres25
 * @version 1.0
 */

/**
 * Objeto con configuraciones predefinidas para DataTables
 */
const DataTableUtils = {
    /**
     * Configuración básica de idioma español para DataTables
     */
    languageConfig: {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningún dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Buscar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Último",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    },

    /**
     * Configuración común para botones de exportación
     * @param {string} title - Título para los reportes
     * @param {string} filename - Nombre del archivo (sin fecha)
     * @param {Array|null} exportColumns - Columnas a exportar
     */
    getExportButtonsConfig: function (title, filename, exportColumns = null) {
        const fecha = new Date().toISOString().slice(0, 10);
        const systemName = 'Sistema Base MVC';

        return [
            {
                extend: 'collection',
                text: 'Reportes',
                buttons: [
                    {
                        extend: 'copy',
                        text: 'Copiar',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        title: title,
                        messageTop: `Registro de ${title.toLowerCase()}`,
                        messageBottom: 'Documento generado el ' + new Date().toLocaleDateString('es-BO'),
                        filename: `${filename}_${fecha}`,
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF',
                        title: title,
                        filename: `${filename}_${fecha}`,
                        pageSize: 'LETTER',
                        exportOptions: {
                            columns: exportColumns
                        },
                        customize: function (doc) {
                            // Estilo básico
                            doc.defaultStyle.fontSize = 10;
                            doc.styles.tableHeader.fontSize = 11;
                            doc.styles.tableHeader.fillColor = '#4b545c';
                            doc.styles.tableHeader.color = '#ffffff';

                            // Título principal
                            doc.content.splice(0, 1, {
                                text: title.toUpperCase(),
                                style: {
                                    fontSize: 16,
                                    alignment: 'center',
                                    bold: true,
                                    margin: [0, 10, 0, 10]
                                }
                            });

                            // Subtítulo
                            doc.content.splice(1, 0, {
                                text: `Registro de ${title.toLowerCase()}`,
                                style: {
                                    fontSize: 11,
                                    alignment: 'center',
                                    italic: true,
                                    margin: [0, 0, 0, 10]
                                }
                            });

                            // Fecha de generación
                            doc.content.splice(2, 0, {
                                text: 'Generado el: ' + new Date().toLocaleString('es-BO'),
                                style: {
                                    fontSize: 9,
                                    alignment: 'right',
                                    margin: [0, 0, 0, 10]
                                }
                            });

                            // Pie de página
                            doc.footer = function (currentPage, pageCount) {
                                return {
                                    columns: [{
                                        text: systemName,
                                        alignment: 'left',
                                        fontSize: 8
                                    },
                                    {
                                        text: 'Página ' + currentPage + ' de ' + pageCount,
                                        alignment: 'center',
                                        fontSize: 8
                                    },
                                    {
                                        text: 'Confidencial',
                                        alignment: 'right',
                                        fontSize: 8
                                    }
                                    ],
                                    margin: [40, 0]
                                };
                            };
                        }
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        exportOptions: {
                            columns: exportColumns
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Imprimir',
                        title: title,
                        messageTop: 'Reporte generado el ' + new Date().toLocaleDateString('es-BO'),
                        exportOptions: {
                            columns: exportColumns
                        },
                        customize: function (win) {
                            $(win.document.body).find('table')
                                .addClass('table-striped')
                                .css('font-size', '12px');
                        }
                    }
                ]
            },
            {
                extend: 'colvis',
                text: 'Visualización de columnas'
            }
        ];
    },

    /**
     * Configuración base para DataTables (sin inicializar)
     * @param {Object} customOptions - Opciones personalizadas a combinar
     * @returns {Object} Objeto de configuración
     */
    getBaseConfig: function (customOptions = {}) {
        const baseConfig = {
            responsive: true,
            autoWidth: false,
            pageLength: 5,
            lengthMenu: [
                [3, 5, 10, 25, 50],
                [3, 5, 10, 25, 50]
            ],
            language: this.languageConfig
        };

        return $.extend(true, {}, baseConfig, customOptions);
    },

    /**
     * Personaliza el mensaje de información para una entidad específica
     * @param {string} entityName - Nombre de la entidad (ej: 'Usuarios', 'Permisos')
     * @returns {Object} Configuración de idioma personalizada
     */
    customizeLanguageFor: function (entityName) {
        // Crear una copia profunda para no modificar el original
        const customLanguage = $.extend(true, {}, this.languageConfig);

        // Personalizar mensajes específicos
        customLanguage.sInfo = `Mostrando registros del _START_ al _END_ de un total de _TOTAL_ ${entityName}`;
        customLanguage.sInfoEmpty = `Mostrando registros del 0 al 0 de un total de 0 ${entityName}`;
        customLanguage.sInfoFiltered = `(filtrado de un total de _MAX_ ${entityName})`;

        return customLanguage;
    }
};

/**
 * Aplica una configuración base de DataTables a una tabla ya inicializada
 * Útil para aplicar configuraciones sin reinicializar la tabla
 * @param {Object} table - Instancia de DataTable
 * @param {Object} options - Opciones para aplicar
 */
function enhanceDataTable(table, options = {}) {
    // Aplicar opciones específicas a la tabla ya inicializada
    if (options.language) {
        table.language = options.language;
    }

    if (options.responsive !== undefined) {
        table.responsive = options.responsive;
    }

    // Refrescar la tabla para aplicar cambios
    table.draw();
}

/**
 * Crea una configuración para DataTables personalizada para una entidad
 * @param {string} entityName - Nombre de la entidad (ej: 'Usuarios', 'Permisos')
 * @param {Array|null} exportColumns - Columnas a exportar
 * @param {Object} customOptions - Opciones adicionales
 * @returns {Object} Configuración completa para DataTables
 */
function createTableConfig(entityName, exportColumns = null, customOptions = {}) {
    // Convertir a formato correcto si es necesario
    const formattedEntityName = entityName.charAt(0).toUpperCase() + entityName.slice(1);
    const filename = entityName.toLowerCase();

    // Crear configuración base
    const config = DataTableUtils.getBaseConfig({
        language: DataTableUtils.customizeLanguageFor(formattedEntityName),
        buttons: DataTableUtils.getExportButtonsConfig(
            `${formattedEntityName} del Sistema`,
            `${filename}_sistema`,
            exportColumns
        )
    });

    // Combinar con opciones personalizadas
    return $.extend(true, {}, config, customOptions);
}