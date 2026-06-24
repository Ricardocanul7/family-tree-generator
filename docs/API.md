# API de Árbol Familiar

## Endpoints

### Obtener árbol completo

```
GET /api/tree/full
```

Retorna el árbol genealógico completo. Si hay múltiples familias raíz, las agrupa bajo un nodo "Familias".

**Respuesta exitosa (una sola raíz):**
```json
{
  "id": 1,
  "name": "Carlos García",
  "first_name": "Carlos",
  "last_name": "García",
  "photo": "https://ui-avatars.com/api/?name=Carlos+García&size=200&background=random",
  "birth_date": "15/03/1950",
  "death_date": null,
  "gender": "male",
  "biography": "Fundador de la familia García. Ingeniero civil jubilado.",
  "children_count": 3,
  "children": [
    {
      "id": 3,
      "name": "Pedro García López",
      "first_name": "Pedro",
      "last_name": "García López",
      "photo": "https://ui-avatars.com/api/?name=Pedro+García+López&size=200&background=random",
      "birth_date": "10/01/1975",
      "death_date": null,
      "gender": "male",
      "biography": "Ingeniero en sistemas. Padre de Miguel y Lucía.",
      "children_count": 2,
      "children": [
        {
          "id": 6,
          "name": "Miguel García",
          "children_count": 0,
          "children": []
        }
      ]
    }
  ]
}
```

**Respuesta exitosa (múltiples raíces):**
```json
{
  "id": 0,
  "name": "Familias",
  "children": [
    { "...primer árbol..." },
    { "...segundo árbol..." }
  ]
}
```

**Respuesta vacía:**
```json
null
```

---

### Obtener árbol por persona

```
GET /api/tree/{id}
```

Retorna el árbol desde una persona específica como raíz, con todos sus descendientes (máximo 10 niveles de profundidad).

**Parámetros de ruta:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `id`      | integer | ID de la persona raíz |

**Respuesta exitosa:**
```json
{
  "id": 3,
  "name": "Pedro García López",
  "first_name": "Pedro",
  "last_name": "García López",
  "photo": "https://ui-avatars.com/api/?name=Pedro+García+López&size=200&background=random",
  "birth_date": "10/01/1975",
  "death_date": null,
  "gender": "male",
  "biography": "Ingeniero en sistemas. Padre de Miguel y Lucía.",
  "children_count": 2,
  "children": [
    {
      "id": 6,
      "name": "Miguel García",
      "children_count": 0,
      "children": []
    },
    {
      "id": 7,
      "name": "Lucía García",
      "children_count": 0,
      "children": []
    }
  ]
}
```

**Errores:**
| Código | Descripción |
|--------|-------------|
| 404    | Persona no encontrada |

---

## Estructura del Nodo

| Campo           | Tipo           | Descripción                      |
|-----------------|----------------|----------------------------------|
| `id`            | integer        | ID de la persona                 |
| `name`          | string         | Nombre completo                  |
| `first_name`    | string         | Nombre                           |
| `last_name`     | string         | Apellido                         |
| `photo`         | string         | URL de la foto o avatar          |
| `birth_date`    | string\|null   | Fecha de nacimiento (d/m/Y)      |
| `death_date`    | string\|null   | Fecha de fallecimiento (d/m/Y)   |
| `gender`        | string\|null   | `male` o `female`                |
| `biography`     | string\|null   | Biografía                        |
| `children_count`| integer        | Número de hijos                  |
| `children`      | array          | Array de nodos hijos (recursivo) |

## Límites

- **Profundidad máxima:** 10 niveles
- Si se supera el límite, `children` será un array vacío para ese nivel
