--# Exportar servicios

select
	i.name as "institution",
	ps.name as "nombre",
	ps.description as "descripcion",
	ps.instructions as "instrucciones",
	ps.requirements as "requisitos",
	ps.cost as "costo",
	ps.time_response as "tiempo_de_respuesta",
	ps.type_of_document_obtainable as "documento_obtenible",
	ps.url as "enlace",
	ps.normative as "respaldo_legal",
	ps.updated_at as "fecha_actualizado"
from
	public_service ps
inner join institution i on
	i.id = ps.institution_id;