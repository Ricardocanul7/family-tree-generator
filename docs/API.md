# Family Tree API

## Endpoints

### Get full tree

```
GET /api/tree/full
```

Returns the complete family tree. If there are multiple root families, they are grouped under a "Families" node.

**Successful response (single root):**
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

**Successful response (multiple roots):**
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

**Empty response:**
```json
null
```

---

### Get tree by person

```
GET /api/tree/{id}
```

Returns the tree from a specific person as root, with all their descendants (maximum 10 levels deep).

**Route parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id`      | integer | Root person ID |

**Successful response:**
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

**Errors:**
| Code | Description |
|------|-------------|
| 404  | Person not found |

---

## Node Structure

| Field           | Type           | Description                      |
|-----------------|----------------|----------------------------------|
| `id`            | integer        | Person ID                        |
| `name`          | string         | Full name                        |
| `first_name`    | string         | First name                       |
| `last_name`     | string         | Last name                        |
| `photo`         | string         | Photo or avatar URL              |
| `birth_date`    | string\|null   | Birth date (d/m/Y)               |
| `death_date`    | string\|null   | Death date (d/m/Y)               |
| `gender`        | string\|null   | `male` or `female`               |
| `biography`     | string\|null   | Biography                        |
| `children_count`| integer        | Number of children               |
| `children`      | array          | Array of child nodes (recursive) |

## Limits

- **Maximum depth:** 10 levels
- If the limit is exceeded, `children` will be an empty array for that level
