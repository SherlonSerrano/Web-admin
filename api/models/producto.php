<?php
require_once '../config/conexion.php';

class Producto
{
    private $conexion;

    // Inicializa la conexión a la base de datos mediante la clase Conexion.
    // Guarda la conexión PDO para utilizarla en las consultas del modelo.
    public function __construct()
    {
        $db = new Conexion(); 
        $this->conexion = $db->conectar();
    }

    // Obtiene todos los productos ordenados por su id, sin paginación.
    // Incluye el nombre de la categoría en el campo "category".
    // LEFT JOIN permite incluir productos aunque no tengan una categoría asociada.
    // No filtra por estado: devuelve productos activos e inactivos.
    public function obtenerProductos()
    {
        $sql = "SELECT
                    p.*,
                    c.title AS category
                FROM productos p
                LEFT JOIN categorias c
                    ON p.categoria_id = c.id
                ORDER BY p.id";

        $rs = $this->conexion->query($sql);

        return $rs->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene los productos que pertenecen a la categoría indicada.
    // Incluye el nombre de la categoría y ordena los resultados por id.
    // Devuelve una lista sin paginación y sin filtrar por estado.
    public function obtenerProductosCategoria($cat)
    {
        $sql = "SELECT
                    p.*,
                    c.title AS category
                FROM productos p
                LEFT JOIN categorias c
                    ON p.categoria_id = c.id
                WHERE p.categoria_id = :cat
                ORDER BY p.id";

        $rs = $this->conexion->prepare($sql);

        $rs->bindValue(':cat', (int) $cat, PDO::PARAM_INT);
        $rs->execute();

        return $rs->fetchAll(PDO::FETCH_ASSOC);
    }

    // Consulta un producto por su id e incluye el nombre de su categoría.
    // Agrega sus registros de imágenes en la propiedad "images".
    // Devuelve null si la consulta no encuentra un producto con categoría coincidente.
    // Si el producto no tiene imágenes, "images" contiene una lista vacía.
    public function obtenerProductoId($id)
    {
        $sql = "SELECT 
                    p.*, 
                    c.title AS category 
                FROM productos p
                JOIN categorias c 
                    ON p.categoria_id = c.id
                WHERE p.id = :id";

        $rs = $this->conexion->prepare($sql);
        $rs->execute([
            ':id' => $id
        ]);

        $data = $rs->fetch(PDO::FETCH_ASSOC);
        // Si no hay resultado, evitar la consulta de imágenes y devolver null.
        if (!$data) {
            return null;
        }
        // Consultar las imágenes asociadas al producto.
        // Cada registro incluye su dirección en la columna "url".
        $sqlImg = "SELECT *
                FROM images
                WHERE product_id = :product_id";
        $rsImg = $this->conexion->prepare($sqlImg);
        $rsImg->execute([
            ':product_id' => $id
        ]);
        // Incorporar la lista de imágenes a los datos que recibirá el controlador.
        $data['images'] = $rsImg->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    // Busca productos por nombre y por límites opcionales de precio.
    // Aplica paginación y permite entre 1 y 100 productos por página.
    // Devuelve "data" con los productos de la página y "total" con la cantidad
    // de coincidencias antes de aplicar la paginación.
    // No filtra por estado: incluye productos activos e inactivos.

    public function filtrarProductos($datos)
    {
        // Preparar el nombre de búsqueda; una cadena vacía no restringe por nombre.
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        // Normalizar la página y limitar la cantidad de resultados por página.
        $pagina = max(1, (int) ($datos['pagina'] ?? 1));
        $porPagina = min(100, max(1, (int) ($datos['por_pagina'] ?? 10)));
        // Calcular cuántos registros se deben omitir para llegar a la página solicitada.
        $offset = ($pagina - 1) * $porPagina;
        // Compartir las mismas tablas y condiciones entre el listado y el conteo.
        $from = " FROM productos p
                LEFT JOIN categorias c
                    ON p.categoria_id = c.id";
        $where = " WHERE 1 = 1";
        $parametros = [];
        // Agregar únicamente los filtros que tengan un valor informado.
        if ($nombre !== '') {
            $where .= " AND p.title LIKE :nombre";
            $parametros[':nombre'] = '%' . $nombre . '%';
        }

        if (
            isset($datos['precio_min']) &&
            $datos['precio_min'] !== ''
        ) {
            $where .= " AND p.price >= :precio_min";
            $parametros[':precio_min'] = $datos['precio_min'];
        }

        if (
            isset($datos['precio_max']) &&
            $datos['precio_max'] !== ''
        ) {
            $where .= " AND p.price <= :precio_max";
            $parametros[':precio_max'] = $datos['precio_max'];
        }
        // Consultar los productos de la página solicitada con un orden estable por id.
        $sql = "SELECT p.*, c.title AS category"
            . $from
            . $where
            . " ORDER BY p.id LIMIT :limit OFFSET :offset";

        $rs = $this->conexion->prepare($sql);
        // Asociar los valores de los filtros a los parámetros de la consulta.
        foreach ($parametros as $clave => $valor) {
            $rs->bindValue($clave, $valor);
        }
        // Enviar el límite y el desplazamiento como valores enteros.
        $rs->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $rs->bindValue(':offset', $offset, PDO::PARAM_INT);
        $rs->execute();

        $data = $rs->fetchAll(PDO::FETCH_ASSOC);
        // Contar todas las coincidencias con los mismos filtros, sin LIMIT ni OFFSET.
        // Este total permite calcular la cantidad de páginas en el frontend.
        $sqlTotal = "SELECT COUNT(*)" . $from . $where;

        $rsTotal = $this->conexion->prepare($sqlTotal);
        $rsTotal->execute($parametros);

        return [
            'data' => $data,
            'total' => (int) $rsTotal->fetchColumn()
        ];
    }

    // Inserta un producto con los datos recibidos del controlador.
    // Registra nombre, precio, descripción, categoría, stock y estado.
    // Devuelve el id generado para identificar el nuevo producto.
    // No registra imágenes ni guarda archivos.
    public function registrarProducto($datos)
    {
        $sql = "INSERT INTO productos 
                    (title, price, description, categoria_id, stock, state)
                VALUES 
                    (:nombre, :precio, :descripcion, :categoria_id, :stock, :state)";

        $rs = $this->conexion->prepare($sql);

        $rs->execute([
            ':nombre' => $datos['nombre'],
            ':precio' => $datos['precio'],
            ':descripcion' => $datos['descripcion'],
            ':categoria_id' => $datos['categoria_id'],
            ':stock' => $datos['stock'],
            ':state' => $datos['state']
        ]);
        // Obtener el identificador autogenerado del producto recién insertado.
        return (int) $this->conexion->lastInsertId();
    }

    // Actualiza nombre, precio, stock y estado del producto indicado por su id.
    // Conserva la descripción, la categoría y los registros de imágenes.
    // Devuelve el resultado de ejecutar la consulta; no confirma por sí solo
    // que el producto exista ni que sus valores hayan cambiado.

    public function actualizarProducto($datos)
    {
        $sql = "UPDATE `productos` 
                SET `title`= :nombre,
                    `price`= :precio,
                    `stock`= :stock,
                    `state`= :state
                WHERE id = :id";

        $rs = $this->conexion->prepare($sql);

        $parametros = [
            ':nombre' => $datos['nombre'],
            ':precio' => $datos['precio'],
            ':stock' => $datos['stock'],
            ':state' => $datos['state'],
            ':id' => $datos['id']
        ];

        return $rs->execute($parametros);
    }

    // Elimina definitivamente un producto y sus registros de imágenes.
    // Primero elimina las referencias en images para respetar la clave foránea.
    // No elimina los archivos físicos de imagen guardados en la carpeta.
    
    public function eliminarProducto($id)
    {
        try {
            // Agrupar los borrados para confirmarlos o cancelarlos juntos.
            $this->conexion->beginTransaction();

            // Eliminar primero los registros de imágenes asociados al producto.
            $sqlImagenes = "DELETE FROM images
                       WHERE product_id = :id";

            $rsImagenes = $this->conexion->prepare($sqlImagenes);

            if (!$rsImagenes->execute([':id' => $id])) {
                throw new Exception(
                    "No se pudieron eliminar los registros de imágenes"
                );
            }

            // Eliminar el producto después de retirar sus referencias.
            $sqlProducto = "DELETE FROM productos
                       WHERE id = :id";

            $rsProducto = $this->conexion->prepare($sqlProducto);

            if (!$rsProducto->execute([':id' => $id])) {
                throw new Exception("No se pudo eliminar el producto");
            }

            // Si el producto no existe, cancelar los cambios.
            if ($rsProducto->rowCount() === 0) {
                $this->conexion->rollBack();
                return false;
            }

            // Confirmar ambos borrados únicamente si la operación fue exitosa.
            $this->conexion->commit();

            return true;
        } catch (Exception $e) {
            // Deshacer los cambios si ocurre un error durante el proceso.
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            // El controlador se encargará de responder al cliente.
            throw $e;
        }
    }
}
