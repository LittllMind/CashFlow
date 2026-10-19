<script>
    var percentOfMales = (<?= $numberOfMales ?> * 100) / <?= $numberOfPasbaPatients ?>;
    var percentOfFemales = (<?= $numberOfFemales ?> * 100) / <?= $numberOfPasbaPatients ?>;
    var percentOfIntersexe = (<?= $numberOfIntersexe ?> * 100) / <?= $numberOfPasbaPatients ?>;

    let pasbaSexeDataPoints = [];

    pasbaSexeDataPoints.push({
        y: percentOfMales,
        label: "Hommes"
    });
    pasbaSexeDataPoints.push({
        y: percentOfFemales,
        label: "Femmes"
    });
    pasbaSexeDataPoints.push({
        y: percentOfIntersexe,
        label: "Non défini"
    });

    var pasba_sexe_chart = new CanvasJS.Chart("pasba_sexe", {
        backgroundColor: "#f2faef",
        animationEnabled: true,
        title: {
            text: "Sexe"
        },
        data: [{
            type: "pie",
            startAngle: 240,
            yValueFormatString: "##0.00\"%\"",
            indexLabel: "{label} {y}",
            dataPoints: pasbaSexeDataPoints
        }]
    });
    pasba_sexe_chart.render();
</script>