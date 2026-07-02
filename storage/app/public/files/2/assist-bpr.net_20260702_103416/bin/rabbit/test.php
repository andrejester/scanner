<?php
	$command 		= "php rabbit_server_ppob.php bpr.mvc.sis2.net";
	$outputFile = "test.txt";

	# jalankan server RPC di latar belakang dan tangkap PIDs
	exec($command . ' > /dev/null 2>&1 & echo $!', $output);

	# Dapatkan PID dari output
	if (isset($output[0])) {
		$pid = $output[0];
		file_put_contents($outputFile, "RPC Server is running with PID: $pid\n");
		echo "RPC Server is running with PID: $pid\n";
	} else {
		echo "Failed to start RPC Server.\n";
	}
?>
