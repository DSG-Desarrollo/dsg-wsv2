<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Tasks;

class TasksController extends Controller
{
    public function getTasks(Request $request)
    {
        $filters = $request->all(); // Obtener todos los parámetros de la solicitud

        $userId = $filters['user_id'] ?? null; // Obtener el ID de usuario si está presente

        $query = Tasks::query();

        foreach ($filters as $key => $value) {
            if ($key === 'user_id' || $value === null) {
                continue; // Saltar el procesamiento de 'user_id' o filtros nulos
            }

            // Aplicar filtros dinámicos a la consulta si el valor del filtro no es nulo
            $query->where($key, $value);
        }

        if ($userId !== null) {
            $query->where('progreso_tarea', '<>', 'S')
                ->whereHas('assignmentsTasks.positionEmployee.employee.user', function ($query) use ($userId) {
                    $query->where('id_usuario', $userId)
                        ->where('estado_asignacion', 'A');
                });
        }

        $tasks = $query->with([
            'customerService',
            'priority',
            'author',
            'revisionUser',
        ])
            ->orderByDesc('registro_fecha')
            ->take(10)
            ->get();


        $formattedTasks = $tasks->map(function ($task) {
            $fechaProgramacion = Carbon::parse($task->fecha_programacion);

            // Obtener el nombre del mes en español
            setlocale(LC_TIME, 'es_ES');

            // Formatear la fecha según los requisitos especificados
            $formattedFechaProgramacion = $fechaProgramacion->format("Y-m-d g:i A");

            // Reemplazar el valor original de 'fecha_programacion' con el formato deseado
            $task->fecha_programacion = $formattedFechaProgramacion;

            return $task;
        });

        return response()->json(['tasks' => $formattedTasks]);
    }

    /*
SELECT t.id_tarea, t.id_cuenta, t.codigo_tarea, t.direccion_tarea, t.orden_requerida, t.orden_completada,
       t.descripcion_tarea, t.comentario_tarea, t.progreso_tarea,
       t.puesto_trabajo, t.fecha_inicio_tarea, t.fecha_fin_tarea, t.fecha_programacion,
       t.solicitud_programacion, t.comentario_programacion, t.id_autorizacion_programacion,
       t.id_usuario_revision, t.id_usuario_revision, t.fecha_revision,
       t.registro_fecha, t.correo_solicitud, t.correo_inicio, t.correo_completo,
       t.id_tipo_tarea, t.codigo_tarea,
        t.fecha_programacion
FROM tareas t
WHERE t.progreso_tarea <> 'S'
AND EXISTS (
    SELECT ast.id_asignacion_tarea
    FROM asignaciones_tareas ast
    INNER JOIN puestos_empleados pe ON ast.id_puesto_empleado = pe.id_puesto_empleado
    INNER JOIN empleados emp ON pe.id_empleado = emp.id_empleado
    INNER JOIN usuarios u ON emp.id_usuario_empleado = u.id_usuario
    WHERE u.id_usuario = 38 AND emp.id_usuario_empleado = 38
    AND ast.id_tarea = t.id_tarea
    AND ast.estado_asignacion = 'A'
)
ORDER BY t.registro_fecha DESC

*/
}
