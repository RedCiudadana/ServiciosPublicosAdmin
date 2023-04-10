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
FROM cypher('graph_name', $$
	MATCH (x:Tramite)
	WHERE x.name = 'Inscripcion patronal' AND x.institution = 'IGGS'
    CREATE  p = 
    	(:Tramite {name: 'Licencia Sanitaria para establecimientos de alimentos preparados', institution: 'MSPAS'})<-[:NEED_OF]-(x)-[:NEED_OF]->(:Tramite {name: 'Licencia ambiental', institution: 'MARN'}) $$)
as (p agtype);
```


```
SELECT * 
FROM cypher('graph_name', $$
    CREATE  p = (:Tramite {name: 'DPI', institution: 'SAT'})-[:NEED_OF]->(:Tramite {name: 'NACIMIENTO', institution: 'SAT'})<-[:NEED_OF]-(:Tramite {name: 'NIT', institution: 'SAT'}) $$)
as (p agtype);
```

```
SELECT * FROM cypher('graph_name', $$
MATCH (v)
RETURN v
$$) as (v agtype);
```

```
SELECT * FROM cypher('graph_name', $$
MATCH (a:Tramite)
RETURN a
$$) as (a agtype);
```

```
SELECT * from cypher('graph_name', $$
        MATCH (V)-[R:NEED_OF]-(V2)
        RETURN V,R,V2
$$) as (V agtype, R agtype, V2 agtype);
```


```
SELECT * 
FROM cypher('graph_name', $$
  MATCH (v:Tramite)
  DETACH DELETE v
$$) as (v agtype);
```