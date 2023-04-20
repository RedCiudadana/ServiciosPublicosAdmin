- Actualizar
- Hacer pruebas de crear rutas
- Probar endpoint con usuario web
- Actualizar cronjob para ejecutarse mas rapido

# TODO
- Mejoras a creación de usuario, roles
- Mejoras a creación de usuario, contraseña
- Cargar servicios publicos por CSV y que se actualizan

# Gobierno
- Más texto en normativas
- Revisar porque en instrucciones no se visualizar correctamente el HTML - https://admin.tramites.redciudadana.org/public/service/1354/edit - https://www.npmjs.com/package/sanitize-html
- Sessiones largas
- Agregar busqueda de ID
- Costo variable, ocultar costo y moneda
- Mostrar fecha de actualización en ADMIN y Publicos
- Eliminar tramites de ministerio de gobernación y carga nueva plantilla
- Agregar moneda en plantilla
- Base de datos actualizada


# CREATE GRAPH

```
SELECT * 
FROM cypher('graph_public_services', $$
	MATCH (x:Tramite)
	WHERE x.name = 'Inscripcion patronal' AND x.institution = 'IGGS'
    CREATE  p = 
    	(:Tramite {name: 'Licencia Sanitaria para establecimientos de alimentos preparados', institution: 'MSPAS'})<-[:NEED_OF]-(x)-[:NEED_OF]->(:Tramite {name: 'Licencia ambiental', institution: 'MARN'}) $$)
as (p agtype);
```


```
SELECT * 
FROM cypher('graph_public_services', $$
    CREATE  p = (:Tramite {name: 'DPI', institution: 'SAT'})-[:NEED_OF]->(:Tramite {name: 'NACIMIENTO', institution: 'SAT'})<-[:NEED_OF]-(:Tramite {name: 'NIT', institution: 'SAT'}) $$)
as (p agtype);
```

```
SELECT * FROM cypher('graph_public_services', $$
MATCH (v)
RETURN v
$$) as (v agtype);
```

```
SELECT * FROM cypher('graph_public_services', $$
MATCH (a:Tramite)
RETURN a
$$) as (a agtype);
```

```
SELECT * from cypher('graph_public_services', $$
        MATCH (V)-[R:NEED_OF]-(V2)
        RETURN V,R,V2
$$) as (V agtype, R agtype, V2 agtype);
```


```
SELECT * 
FROM cypher('graph_public_services', $$
  MATCH (v:Tramite)
  DETACH DELETE v
$$) as (v agtype);
```

Get all services related to a route

```
SELECT * from cypher('graph_public_services', $$
        MATCH (V:Route {identifier: '2'})-[R:NEED_OF *]-(V2)
        RETURN V,R,V2
$$) as (V agtype, R agtype, V2 agtype);
```

- Al agregar una dependencia se agrega un vertex si no existe, se agrega un edge de la ruta, aunque el vertex ya tenga el mismo edge de otra ruta. Es redudante por si en algun momento varian de ruta. Pendiente funcionalidad para copiar opcionalmente las relaciones de un vertex de una ruta a otra.

- Al eliminar una dependencia eliminar unicamente el primer edge ingoing al vertex(dependecia) a eliminar. Esto puede provocar edges huerfanos a la ruta, no deberia afectar las rutas y es data que no perderemos, o podemos eliminar mas adelante.